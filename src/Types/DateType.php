<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

use Ang3\Component\Odoo\DBAL\Config\DatabaseSettings;

class DateType extends Type
{
    /**
     * Context parameters keys.
     */
    public const TIMEZONE_KEY = 'timezone';

    public function getName(): string
    {
        return Types::DATE;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): ?string
    {
        if (!$value) {
            return null;
        }

        $timezone = $this->getTimezone($context);

        if (\is_string($value)) {
            try {
                $date = new \DateTime($value, $timezone);
            } catch (\Exception $exception) {
                throw ConversionException::conversionToDatabaseFailed($value, \DateTimeInterface::class, $exception);
            }
        } else {
            if (!$value instanceof \DateTimeInterface) {
                throw ConversionException::unexpectedType($value, \DateTimeInterface::class, ['string', \DateTimeInterface::class]);
            }

            $date = \DateTime::createFromInterface($value);
            $date->setTimezone($timezone);
        }

        return $date->format($this->getFormat());
    }

    public function convertToPhpValue(mixed $value, array $context = []): ?\DateTime
    {
        if (!$value) {
            return null;
        }

        if (!\is_string($value)) {
            throw ConversionException::unexpectedDatabaseFormat($value, self::class, 'string');
        }

        try {
            return new \DateTime($value, $this->getTimezone($context));
        } catch (\Exception $exception) {
            throw ConversionException::conversionToPhpFailed($value, \DateTimeInterface::class, $exception);
        }
    }

    protected function getFormat(): string
    {
        return 'Y-m-d';
    }

    protected function getTimezone(array $context): \DateTimeZone
    {
        return new \DateTimeZone($context[self::TIMEZONE_KEY] ?? DatabaseSettings::DEFAULT_TIMEZONE);
    }
}
