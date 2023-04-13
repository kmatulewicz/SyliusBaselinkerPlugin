<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Fixtures\Factory;

use DateTimeInterface;
use InvalidArgumentException;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\OrderExampleFactory as BaseOrderExampleFactory;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderExampleFactory extends BaseOrderExampleFactory implements ExampleFactoryInterface
{

    public function create(array $options = []): OrderInterface
    {
        $options = $this->optionsResolver->resolve($options);

        $order = $this->createOrder(
            $options['channel'],
            $options['customer'],
            $options['country'],
            $options['complete_date'],
            $options['items'],
            $options['shipping_address'],
            $options['billing_address'],
            $options['note'],
        );
        $this->setOrderCompletedDate($order, $options['complete_date']);
        if ($options['fulfilled']) {
            $this->fulfillOrder($order);
        }

        return $order;
    }

    protected function createOrder(
        ChannelInterface $channel,
        CustomerInterface $customer,
        CountryInterface $country,
        DateTimeInterface $createdAt,
        array $items = [],
        ?array $shippingAddress = null,
        ?array $billingAddress = null,
        ?string $note = null,
    ): OrderInterface {
        $countryCode = $country->getCode();

        $currencyCode = $channel->getBaseCurrency()->getCode();
        $localeCode = $this->faker->randomElement($channel->getLocales()->toArray())->getCode();

        /** @var OrderInterface $order */
        $order = $this->orderFactory->createNew();
        $order->setChannel($channel);
        $order->setCustomer($customer);
        $order->setCurrencyCode($currencyCode);
        $order->setLocaleCode($localeCode);

        $this->generateItems($order, $items);

        if (null !== $shippingAddress && null !== $billingAddress) {
            $order->setShippingAddress($this->getAddress($shippingAddress));
            $order->setBillingAddress($this->getAddress($billingAddress));
            $this->applyCheckoutStateTransition($order, OrderCheckoutTransitions::TRANSITION_ADDRESS);
        } else {
            $this->address($order, $countryCode);
        }

        $this->selectShipping($order, $createdAt);
        $this->selectPayment($order, $createdAt);
        $this->completeCheckout($order, $note);

        return $order;
    }

    protected function generateItems(BaseOrderInterface $order, array $items = []): void
    {
        if (empty($items)) {
            parent::generateItems($order);
            return;
        }

        $channel = $order->getChannel();

        foreach ($items as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException('Product name should be string type.');
            }
            /** @var ProductInterface $product */
            $product = $this->productRepository->findOneByChannelAndCode($channel, $item);
            if (null === $product) {
                throw new InvalidArgumentException(sprintf(
                    'No product found by code "%s".',
                    $item
                ));
            }

            $variant = $product->getVariants()->first();
            /** @var OrderItemInterface $item */
            $item = $this->orderItemFactory->createNew();

            $item->setVariant($variant);
            $this->orderItemQuantityModifier->modify($item, 3);

            $order->addItem($item);
        }
    }

    protected function getAddress(array $array): AddressInterface
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->createNew();
        $address->setFirstName($array['first_name']);
        $address->setLastName($array['last_name']);
        $address->setPhoneNumber($array['phone_number']);
        $address->setCompany($array['company']);
        $address->setStreet($array['street']);
        $address->setCountryCode($array['country_code']);
        $address->setCity($array['city']);
        $address->setPostcode($array['postcode']);

        return $address;
    }

    protected function completeCheckout(BaseOrderInterface $order, ?string $note = null): void
    {
        if (null === $note) {
            if ($this->faker->boolean(25)) {
                $order->setNotes($this->faker->sentence);
            }
        } else {
            $order->setNotes($note);
        }

        $this->applyCheckoutStateTransition($order, OrderCheckoutTransitions::TRANSITION_COMPLETE);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('note', null)
            ->setAllowedTypes('note', ['null', 'string'])
            ->setDefault('items', [])
            ->setAllowedTypes('items', ['array'])
            ->setDefault('shipping_address', null)
            ->setAllowedTypes('shipping_address', ['null', 'array'])
            ->setDefault('billing_address', null)
            ->setAllowedTypes('billing_address', ['null', 'array']);
    }
}
