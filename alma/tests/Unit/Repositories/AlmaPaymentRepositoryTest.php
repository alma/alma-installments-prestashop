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

namespace Alma\PrestaShop\Tests\Unit\Repositories;

use Alma\PrestaShop\Repositories\AlmaPaymentRepository;
use PHPUnit\Framework\TestCase;

class AlmaPaymentRepositoryTest extends TestCase
{
    /** @var AlmaPaymentRepository */
    private $repository;

    /** @var \Db|\PHPUnit\Framework\MockObject\MockObject */
    private $dbMock;

    public function setUp()
    {
        $this->dbMock = $this->createMock(\Db::class);
        \Db::setInstance($this->dbMock);
        $this->repository = new AlmaPaymentRepository();
    }

    public function tearDown()
    {
        $this->repository = null;
    }

    public function testCreateTableExecutesCreateStatement()
    {
        $this->dbMock->expects($this->once())
            ->method('execute')
            ->with($this->stringContains('CREATE TABLE IF NOT EXISTS'))
            ->willReturn(true);

        $this->assertTrue($this->repository->createTable());
    }

    public function testCreateTableIncludesUniqueKeyConstraint()
    {
        $this->dbMock->expects($this->once())
            ->method('execute')
            ->with($this->stringContains('uq_cart_captured'))
            ->willReturn(true);

        $this->repository->createTable();
    }

    public function testInsertCaptureReturnsTrueOnSuccess()
    {
        $this->dbMock->expects($this->once())
            ->method('insert')
            ->with(
                'alma_payment',
                $this->callback(function ($data) {
                    return $data['id_cart'] === 42
                        && $data['alma_payment_id'] === 'pay_test_123'
                        && $data['status'] === AlmaPaymentRepository::STATUS_CAPTURED;
                })
            )
            ->willReturn(true);

        $this->assertTrue($this->repository->insertCapture(42, 'pay_test_123'));
    }

    public function testInsertCaptureReturnsFalseOnDuplicateEntry1062()
    {
        $duplicateException = new \PrestaShopDatabaseException('Duplicate entry for key uq_cart_captured — 1062');

        $this->dbMock->expects($this->once())
            ->method('insert')
            ->willThrowException($duplicateException);

        $this->assertFalse($this->repository->insertCapture(42, 'pay_test_123'));
    }

    public function testInsertCaptureReturnsFalseOnDuplicateEntryMessage()
    {
        $duplicateException = new \PrestaShopDatabaseException('Duplicate entry \'42-CAPTURED\' for key \'uq_cart_captured\'');

        $this->dbMock->expects($this->once())
            ->method('insert')
            ->willThrowException($duplicateException);

        $this->assertFalse($this->repository->insertCapture(42, 'pay_test_456'));
    }

    public function testInsertCaptureRethrowsUnexpectedDatabaseException()
    {
        $unexpectedException = new \PrestaShopDatabaseException('Table does not exist');

        $this->dbMock->expects($this->once())
            ->method('insert')
            ->willThrowException($unexpectedException);

        $this->expectException(\PrestaShopDatabaseException::class);
        $this->expectExceptionMessage('Table does not exist');

        $this->repository->insertCapture(42, 'pay_test_789');
    }

    public function testDeleteByCartIdExecutesDeleteStatement()
    {
        $this->dbMock->expects($this->once())
            ->method('delete')
            ->with('alma_payment', '`id_cart` = 42')
            ->willReturn(true);

        $this->assertTrue($this->repository->deleteByCartId(42));
    }

    public function testStatusConstantsAreCorrect()
    {
        $this->assertSame('PENDING', AlmaPaymentRepository::STATUS_PENDING);
        $this->assertSame('CAPTURED', AlmaPaymentRepository::STATUS_CAPTURED);
        $this->assertSame('FAILED', AlmaPaymentRepository::STATUS_FAILED);
    }
}
