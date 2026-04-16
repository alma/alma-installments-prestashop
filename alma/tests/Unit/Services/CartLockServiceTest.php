<?php
/**
 * 2018-2024 Alma SAS.
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\PrestaShop\Services\CartLockService;
use PHPUnit\Framework\TestCase;

class CartLockServiceTest extends TestCase
{
    /** @var CartLockService */
    private $cartLockService;

    /** @var \Db|\PHPUnit\Framework\MockObject\MockObject */
    private $dbMock;

    public function setUp()
    {
        $this->dbMock = $this->createMock(\Db::class);
        \Db::setInstanceForTesting($this->dbMock);
        $this->cartLockService = new CartLockService();
    }

    public function tearDown()
    {
        $this->cartLockService = null;
    }

    public function testAcquireLockReturnsTrueWhenMySQLGrantsLock()
    {
        $this->dbMock->expects($this->once())
            ->method('getValue')
            ->with($this->stringContains("GET_LOCK('prefix_42', 10)"))
            ->willReturn('1');

        $this->assertTrue($this->cartLockService->acquireLock('prefix_42'));
    }

    public function testAcquireLockExecutedTwiceWithoutReleaseSecondReturnFalse()
    {
        $this->dbMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive([$this->stringContains("GET_LOCK('prefix_42', 10)")], [$this->stringContains("GET_LOCK('prefix_42', 10)")])
            ->willReturnOnConsecutiveCalls('1', '0');

        $this->assertTrue($this->cartLockService->acquireLock('prefix_42'));
        $this->assertFalse($this->cartLockService->acquireLock('prefix_42'));
    }

    public function testAcquireLockExecutedTwiceWithReleaseSecondReturnTrue()
    {
        $this->dbMock->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                [$this->stringContains("GET_LOCK('prefix_42', 10)")],
                [$this->stringContains("GET_LOCK('prefix_42', 10)")],
                [$this->stringContains("SELECT RELEASE_LOCK('prefix_42')")]
            )
            ->willReturnOnConsecutiveCalls('1', '1', '1');

        $this->assertTrue($this->cartLockService->acquireLock('prefix_42'));
        $this->assertTrue($this->cartLockService->acquireLock('prefix_42'));
        $this->assertTrue($this->cartLockService->releaseLock());
    }

    public function testAcquireLockReturnsFalseOnTimeout()
    {
        $this->dbMock->expects($this->once())
            ->method('getValue')
            ->willReturn('0');

        $this->assertFalse($this->cartLockService->acquireLock('prefix_42'));
    }

    public function testReleaseLockReturnsTrueWhenSuccessful()
    {
        $this->dbMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnOnConsecutiveCalls('1', '1');

        $this->cartLockService->acquireLock('prefix_42');
        $this->assertTrue($this->cartLockService->releaseLock());
    }

    public function testReleaseLockReturnsFalseWhenLockNotHeld()
    {
        $this->dbMock->expects($this->never())
            ->method('getValue');

        $this->assertFalse($this->cartLockService->releaseLock());
    }

    public function testAcquireLockUsesCustomTimeout()
    {
        $this->dbMock->expects($this->once())
            ->method('getValue')
            ->with($this->stringContains("GET_LOCK('prefix_5', 30)"))
            ->willReturn('1');

        $this->cartLockService->acquireLock('prefix_5', 30);
    }
}
