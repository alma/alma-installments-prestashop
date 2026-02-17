<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\Merchant;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Service\AuthenticationService;

class AuthenticationServiceTest extends TestCase
{
    /**
     * @var Merchant
     */
    private $merchant;

    public function setUp(): void
    {
        $this->merchant = $this->createMock(Merchant::class);
        $this->merchantEndpoint = $this->createMock(MerchantEndpoint::class);
        $this->authenticationService = new AuthenticationService(
            $this->merchantEndpoint,
        );
    }

    public function testGetMerchantIdErrorThrowException(): void
    {
        $this->merchantEndpoint->expects($this->once())
            ->method('me')
            ->willThrowException(new MerchantEndpointException());
        $this->expectException(AuthenticationException::class);
        $this->authenticationService->getMerchantId();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function testGetMerchantId(): void
    {
        $this->merchant->method('getId')
            ->willReturn('42');
        $this->merchantEndpoint->expects($this->once())
            ->method('me')
            ->willReturn($this->merchant);
        $this->assertEquals('42', $this->authenticationService->getMerchantId());
    }

    public function testIsAuthenticatedNoReturnFalse(): void
    {
        $this->merchantEndpoint->expects($this->once())
            ->method('me')
            ->willThrowException(new MerchantEndpointException());

        $this->assertFalse($this->authenticationService->isAuthenticated());
    }

    public function testIsAuthenticatedYesReturnTrue(): void
    {
        $this->merchant->method('getId')
            ->willReturn('123');
        $this->merchantEndpoint->expects($this->once())
            ->method('me')
            ->willReturn($this->merchant);

        $this->assertTrue($this->authenticationService->isAuthenticated());
    }
}
