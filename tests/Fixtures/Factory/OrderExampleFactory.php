<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Fixtures\Factory;

use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\OrderExampleFactory as BaseOrderExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderExampleFactory extends BaseOrderExampleFactory implements ExampleFactoryInterface
{

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('amount', 20)

            ->setDefault('channel', LazyOption::randomOne($this->channelRepository))
            ->setAllowedTypes('channel', ['null', 'string', ChannelInterface::class])
            ->setNormalizer('channel', LazyOption::getOneBy($this->channelRepository, 'code'))

            ->setDefault('customer', LazyOption::randomOne($this->customerRepository))
            ->setAllowedTypes('customer', ['null', 'string', CustomerInterface::class])
            ->setNormalizer('customer', LazyOption::getOneBy($this->customerRepository, 'email'))

            ->setDefault('country', LazyOption::randomOne($this->countryRepository))
            ->setAllowedTypes('country', ['null', 'string', CountryInterface::class])
            ->setNormalizer('country', LazyOption::findOneBy($this->countryRepository, 'code'))

            ->setDefault('complete_date', fn (Options $options): \DateTimeInterface => $this->faker->dateTimeBetween('-13 days', 'now'))
            ->setAllowedTypes('complete_date', ['null', \DateTime::class])

            ->setDefault('fulfilled', false)
            ->setAllowedTypes('fulfilled', ['bool'])

            ->setDefault('items', [])
            ->setAllowedTypes('items', ['array']);
    }

    public function create(array $options = []): OrderInterface
    {
        $options = $this->optionsResolver->resolve($options);

        $order = $this->createOrder(
            $options['channel'],
            $options['customer'],
            $options['country'],
            $options['complete_date'],
            $options['items'],
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
        \DateTimeInterface $createdAt,
        array $items = [],
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

        $this->address($order, $countryCode);
        $this->selectShipping($order, $createdAt);
        $this->selectPayment($order, $createdAt);
        $this->completeCheckout($order);

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
                throw new \InvalidArgumentException('Product name should be string type.');
            }
            /** @var ProductInterface $product */
            $product = $this->productRepository->findOneByChannelAndCode($channel, $item);
            if (null === $product) {
                throw new \InvalidArgumentException(sprintf(
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
}
