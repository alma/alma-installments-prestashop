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

use Alma\PrestaShop\Repositories\CustomerThreadRepository;
use Alma\PrestaShop\Services\MessageOrderService;
use PHPUnit\Framework\TestCase;

class MessageOrderServiceTest extends TestCase
{
    /**
     * @var MessageOrderService
     */
    protected $messageOrderService;

    public function setUp()
    {
        $idCustomer = 1;
        $this->context = $this->createMock(\Context::class);
        $this->module = $this->createMock(\Module::class);
        $this->customerThread = $this->createMock(\CustomerThread::class);
        $this->customerMessage = $this->createMock(\CustomerMessage::class);
        $this->customerThreadRepository = $this->createMock(CustomerThreadRepository::class);
        $this->order = $this->createMock(\Order::class);
        $this->context->language = $this->createMock(\Language::class);
        $this->context->shop = $this->createMock(\Shop::class);

        $this->messageOrderService = new MessageOrderService(
            $idCustomer,
            $this->context,
            $this->module,
            $this->customerThread,
            $this->customerMessage,
            $this->customerThreadRepository
        );
    }

    /**
     * Given the MessageOrderService info,
     * we create a customer thread and add a customer message
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function testCreateCustomerThreadAndAddCustomerMessage()
    {
        $order = $this->order;
        $order->id_customer = 1;
        $order->id = 1;
        $this->context->shop->id = 1;
        $this->context->language->id = 1;
        $idProductInsurance = 20;
        $idCustomerThread = null;
        $messageText = 'text message order test';

        $this->customerMessage->expects($this->once())
            ->method('add')
            ->willReturn(true);
        $this->customerThread->expects($this->once())
            ->method('add')
            ->willReturn(true);
        $this->messageOrderService->addCustomerMessageOnThread(
            $order,
            $idProductInsurance,
            $idCustomerThread,
            $messageText
        );
    }

    /**
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function testUpdateCustomerThreadAndAddCustomerMessage()
    {
        $order = $this->order;
        $order->id_customer = 1;
        $order->id = 1;
        $idProductInsurance = 20;
        $idCustomerThread = 1;
        $messageText = 'text message order test';

        $this->customerMessage->expects($this->once())
            ->method('add')
            ->willReturn(true);
        $this->customerThread->expects($this->once())
            ->method('update')
            ->willReturn(true);
        $this->messageOrderService->addCustomerMessageOnThread(
            $order,
            $idProductInsurance,
            $idCustomerThread,
            $messageText
        );
    }
}
