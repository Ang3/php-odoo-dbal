<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Schema;

class Choice
{
    public function __construct(
        private readonly string $name,
        private readonly string $value,
        private readonly ?int $id = null
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
