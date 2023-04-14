<?php

namespace Tests\SyliusBaselinkerPlugin\Unit\DataProvider;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use SyliusBaselinkerPlugin\DataProvider\PaymentDataProvider;
use SyliusBaselinkerPlugin\DataProvider\PaymentDataProviderInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\once;

final class PaymentDataProviderTest extends TestCase
{

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\PaymentDataProvider
     */
    public function test__implements_payment_data_provider(): void
    {
        self::assertInstanceOf(PaymentDataProviderInterface::class, $this->p());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\PaymentDataProvider
     */
    public function test_external_payment_id(): void
    {
        assertEquals(
            15,
            $this->p('getId', 15)->external_payment_id(),
        );

        assertEquals('', $this->p()->external_payment_id());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\PaymentDataProvider
     */
    public function test_order_id(): void
    {
        $provider = new PaymentDataProvider();
        assertEquals(0, $provider->order_id());

        $order = $this->createMock(OrderInterface::class);
        $order
            ->expects(once())
            ->method('getBaselinkerId')
            ->willReturn(236);
        $provider->setOrder($order);
        assertEquals(236, $provider->order_id());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\PaymentDataProvider
     */
    public function test_payment_comment(): void
    {
        $method = $this->createMock(PaymentMethodInterface::class);
        $method
            ->expects(once())
            ->method('getName')
            ->willReturn('someName');
        assertEquals(
            'someName',
            $this->p('getMethod', $method)
            ->payment_comment()
        );


        $method = $this->createMock(PaymentMethodInterface::class);
        $method
            ->expects(once())
            ->method('getName')
            ->willReturn(null);
        assertEquals(
            '',
            $this->p('getMethod', $method)
                ->payment_comment()
        );

        assertEquals('', $this->p()->payment_comment());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\PaymentDataProvider
     */
    public function test_payment_date(): void
    {
        $date = new \DateTime('yesterday');
        assertEquals(
            $date->getTimestamp(),
            $this->p('getUpdatedAt', $date)->payment_date()
        );

        assertEquals(0, $this->p()->payment_date());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\PaymentDataProvider
     */
    public function test_payment_done(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $payment
            ->expects(once())
            ->method('getState')
            ->willReturn('completed');
        $payment
            ->expects(once())
            ->method('getAmount')
            ->willReturn(3069);
        $order = $this->createMock(OrderInterface::class);
        $order
            ->expects(once())
            ->method('getLastPayment')
            ->willReturn($payment);
        $provider = new PaymentDataProvider();
        $provider->setOrder($order);
        assertEquals(30.69, $provider->payment_done());

        assertEquals(0, $this->p('getState', 'notCompleted')->payment_done());

        assertEquals(0, $this->p()->payment_done());
    }

    /**
     * Generates OrderInterface mock with PaymentInterface mock containing a method
     * which name is passed in $method, that return the result passed in $return.
     * The method need to be run once.
     *
     * @param ?string $method Method name
     * @param mixed $return Will return
     * @return OrderInterface
     */
    private function o(?string $method = null, mixed $return = null): OrderInterface
    {
        $payment = $this->getMockForAbstractClass(PaymentInterface::class);
        if (null !== $method) {
            $payment
                ->expects(self::once())
                ->method($method)
                ->willReturn($return);
        }

        $order = $this->createMock(OrderInterface::class);
        $order
            ->expects(self::once())
            ->method('getLastPayment')
            ->willReturn($payment);

        return $order;
    }

    /**
     * Generates PaymentDataProvider with set OrderInterface mock,
     * with PaymentInterface mock containing a method
     * which name is passed in $orderMethod, that return the result
     * passed in $orderReturn. The method need to be run once.
     *
     * @param ?string $orderMethod OrderItem method name
     * @param mixed $orderReturn OrderItem method will return
     * @return PaymentDataProvider
     */
    private function p(?string $orderMethod = null, mixed $orderReturn = null): PaymentDataProvider
    {
        $provider = new PaymentDataProvider();
        if (null !== $orderMethod) {
            $provider->setOrder($this->o($orderMethod, $orderReturn));
        }

        return $provider;
    }
}
