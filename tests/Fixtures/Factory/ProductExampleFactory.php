<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Fixtures\Factory;

use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ProductExampleFactory as BaseProductExampleFactory;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Component\Product\Generator\ProductVariantGeneratorInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductExampleFactory extends BaseProductExampleFactory implements ExampleFactoryInterface
{
    private OptionsResolver $optionsResolver;

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(
        private FactoryInterface $productFactory,
        private FactoryInterface $productVariantFactory,
        private FactoryInterface $channelPricingFactory,
        private ProductVariantGeneratorInterface $variantGenerator,
        private FactoryInterface $productAttributeValueFactory,
        private FactoryInterface $productImageFactory,
        private FactoryInterface $productTaxonFactory,
        private ImageUploaderInterface $imageUploader,
        private SlugGeneratorInterface $slugGenerator,
        private RepositoryInterface $taxonRepository,
        private RepositoryInterface $productAttributeRepository,
        private RepositoryInterface $productOptionRepository,
        private RepositoryInterface $channelRepository,
        private RepositoryInterface $localeRepository,
        private ?RepositoryInterface $taxCategoryRepository = null,
        private ?FileLocatorInterface $fileLocator = null,
    ) {
        parent::__construct(
            $productFactory,
            $productVariantFactory,
            $channelPricingFactory,
            $variantGenerator,
            $productAttributeValueFactory,
            $productImageFactory,
            $productTaxonFactory,
            $imageUploader,
            $slugGenerator,
            $taxonRepository,
            $productAttributeRepository,
            $productOptionRepository,
            $channelRepository,
            $localeRepository,
            $taxCategoryRepository,
            $fileLocator,
        );

        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {

        parent::configureOptions($resolver);
        $resolver
            ->setDefault('price', 10000)
            ->setAllowedTypes('price', ['integer', 'null']);
    }

    public function create(array $options = []): ProductInterface
    {
        $product = parent::create($options);
        $options = $this->optionsResolver->resolve($options);

        $this->setFixedPrices($product, $options['price'] ?? 10000);

        return $product;
    }

    protected function setFixedPrices(ProductInterface $product, int $price): void
    {
        /** @var ProductVariantInterface $variant */
        foreach ($product->getVariants() as $variant) {
            /** @var ChannelInterface $channel */
            foreach ($this->channelRepository->findAll() as $channel) {
                /** @var ChannelPricingInterface $channelPricing */
                $channelPricing = $this->channelPricingFactory->createNew();
                $channelPricing->setChannelCode($channel->getCode());
                $channelPricing->setPrice($price);

                $variant->addChannelPricing($channelPricing);
            }
        }
    }
}
