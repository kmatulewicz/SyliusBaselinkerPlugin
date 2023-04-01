<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Grid\FieldType;

use Sylius\Component\Grid\DataExtractor\DataExtractorInterface;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Contracts\Translation\TranslatorInterface as TranslationTranslatorInterface;

class TranslatableStringType implements FieldTypeInterface
{
    private TranslationTranslatorInterface $translator;

    private DataExtractorInterface $dataExtractor;

    public function __construct(TranslationTranslatorInterface $translator, DataExtractorInterface $dataExtractor)
    {
        $this->translator = $translator;
        $this->dataExtractor = $dataExtractor;
    }

    public function render(Field $field, $data, array $options = []): string
    {
        /** @var mixed $value */
        $value = $this->dataExtractor->get($field, $data);

        $value = (is_string($value)) ? $value : '';

        $translation = $this->translator->trans('baselinker.settings.' . $value);

        return htmlspecialchars($translation);
    }

    public function configureOptions(mixed $resolver): void
    {
    }

    public function getName(): string
    {
        return 'translatable_string';
    }
}
