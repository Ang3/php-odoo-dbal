<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Query;

class NoUniqueResultException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('The query returned more than one result.');
    }
}
