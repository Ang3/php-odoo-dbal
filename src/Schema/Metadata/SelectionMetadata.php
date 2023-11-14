<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Metadata;

class SelectionMetadata
{
    /**
     * @var ChoiceMetadata[]
     */
    private array $choices;

    /**
     * @param string|ChoiceMetadata[] $choices
     */
    public function __construct(array|string $choices = null)
    {
        /** @var ChoiceMetadata[] $choices */
        $choices = \is_string($choices) ? (array) json_decode($choices, true) : (array) $choices;

        foreach ($choices as $choice) {
            $this->addChoice($choice);
        }
    }

    public function getIds(): array
    {
        $ids = [];

        foreach ($this->choices as $choice) {
            $ids[] = $choice->getId();
        }

        return $ids;
    }

    public function getNames(): array
    {
        $names = [];

        foreach ($this->choices as $choice) {
            $names[] = $choice->getName();
        }

        return $names;
    }

    public function getValues(): array
    {
        $values = [];

        foreach ($this->choices as $choice) {
            $values[] = $choice->getValue();
        }

        return $values;
    }

    /**
     * @return ChoiceMetadata[]
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    public function setChoices(array $choices): self
    {
        $this->choices = [];

        foreach ($choices as $choice) {
            $this->addChoice($choice);
        }

        return $this;
    }

    public function addChoice(ChoiceMetadata $choice): self
    {
        $this->choices[] = $choice;

        return $this;
    }
}
