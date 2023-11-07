<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\RecordManager;

abstract class AbstractQuery implements QueryInterface
{
    protected string $method;
    protected array $parameters = [];
    protected array $options = [];

    final public function __construct(
        protected readonly RecordManager $recordManager,
        protected string $name,
        string $method
    ) {
        $this->setMethod($method);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters = []): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options = []): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Adds an option on the query.
     */
    public function setOption(string $name, mixed $value): static
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Removes an option from the query.
     */
    public function removeOption(string $name): static
    {
        if ($this->hasOption($name)) {
            unset($this->options[$name]);
        }

        return $this;
    }

    /**
     * Gets option by name.
     */
    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    private function hasOption(string $name): bool
    {
        return \array_key_exists($name, $this->options);
    }

    /**
     * Add an option on the query.
     */
    public function clearOptions(): static
    {
        $this->options = [];

        return $this;
    }

    /**
     * Duplicates the query to another instance and/or record manager.
     */
    public function duplicate(RecordManager $recordManager = null): static
    {
        $query = new static($recordManager ?: $this->recordManager, $this->name, $this->method);
        $query->setParameters($this->parameters);
        $query->setOptions($this->options);

        return $query;
    }

    /**
     * Executes the query.
     * Allowed methods: all.
     */
    public function execute(): mixed
    {
        return $this->recordManager->executeQuery($this);
    }

    /**
     * Gets the related manager of the query.
     */
    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }
}
