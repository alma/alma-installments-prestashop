<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider;
use PrestaShop\Module\Alma\Application\Service\AuthenticationService;
use PrestaShop\Module\Alma\Infrastructure\Factory\CurlClientFactory;

class AuthenticationServiceTest extends TestCase
{
    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;

    public function setUp(): void
    {
        $this->settingsProvider = $this->createMock(AuthenticationSettingsProvider::class);
        $this->curlClientFactory = $this->createMock(CurlClientFactory::class);
        $this->authenticationService = new AuthenticationService(
            $this->settingsProvider,
            $this->curlClientFactory
        );
    }

    public function testIsValidKeysWithOneKeyEmptyAndOneKeyInvalid(): void
    {
        $apiKeys = [
            'test' => '',
            'live' => 'invalid_key'
        ];

        $this->settingsProvider->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($apiKeys);

        $this->curlClientFactory->expects($this->once())
            ->method('create')
            ->with('invalid_key', 'live')
            ->willThrowException(new MerchantEndpointException());

        $this->expectException(AuthenticationException::class);
        $this->authenticationService->isValidKeys();
    }

    public function testIsValidKeysWithBothKeyInvalid(): void
    {
        $apiKeys = [
            'test' => 'invalid_key_test',
            'live' => 'invalid_key_live'
        ];

        $this->settingsProvider->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($apiKeys);

        $this->curlClientFactory->expects($this->once())
            ->method('create')
            ->with('invalid_key_test', 'test')
            ->willThrowException(new MerchantEndpointException());

        $this->expectException(AuthenticationException::class);
        $this->authenticationService->isValidKeys();
    }

    public function testSameMerchantIdsWithDifferentIds(): void
    {
        $merchantIds = [
            'test' => '123',
            'live' => '456'
        ];

        $this->expectException(AuthenticationException::class);
        $this->authenticationService->checkSameMerchantIds($merchantIds);
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function testSameMerchantIdsWithSameIds(): void
    {
        $merchantIds = [
            'test' => '123',
            'live' => '123'
        ];

        $this->assertNull($this->authenticationService->checkSameMerchantIds($merchantIds));
    }
}
