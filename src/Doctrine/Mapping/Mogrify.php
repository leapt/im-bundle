<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Doctrine\Mapping;

use Doctrine\Common\Annotations\Annotation;

/**
 * Annotation definition class.
 *
 * @Annotation
 * @codeCoverageIgnore
 */
class Mogrify extends Annotation
{
    /** @var array */
    public $params;
}
