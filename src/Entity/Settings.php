<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity]
#[Table('baselinker_settings')]
class Settings implements ResourceInterface
{
    #[Id]
    #[Column(name: 'name', type: Types::STRING)]
    #[NotNull]
    #[GeneratedValue('NONE')]
    private string $name;

    #[Column(name: 'value', type: Types::STRING)]
    private string $value = '';

    public function __construct(string $name, string $value = '')
    {
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * Get the value of name
     */
    public function getId(): string
    {
        return $this->name;
    }

    /**
     * Get the value of name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     */
    private function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set the value of value
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
