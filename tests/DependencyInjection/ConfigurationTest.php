<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ConfigurationTest extends KernelTestCase
{
    public function testLoad(): void
    {
        // Make sure default config loads without issues
        self::bootKernel();
        self::assertSame(
            'cache/im',
            self::getContainer()->getParameter('leapt_im.cache_path'),
        );
    }
}
