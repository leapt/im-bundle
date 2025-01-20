<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\Listener\Fixtures\Enums;

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}
