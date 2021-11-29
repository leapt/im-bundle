<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Doctrine\Mapping;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Mogrify
{
    public function __construct(public array|string $params)
    {
    }
}
