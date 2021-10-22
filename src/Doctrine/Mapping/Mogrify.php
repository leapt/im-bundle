<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Doctrine\Mapping;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @codeCoverageIgnore
 */
class Mogrify extends Annotation
{
    /**
     * @var array<string>
     */
    public array $params;
}
