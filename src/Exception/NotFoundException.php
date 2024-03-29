<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException implements ExceptionInterface
{
}
