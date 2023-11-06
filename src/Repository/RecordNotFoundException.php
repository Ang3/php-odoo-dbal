<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Repository;

class RecordNotFoundException extends \RuntimeException
{
    public function __construct(private readonly string $modelName, private readonly int $id)
    {
        parent::__construct(sprintf('No record found for model "%s" with ID #%d.', $this->modelName, $this->id));
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
