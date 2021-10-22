<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\Twig\Extension;

use Leapt\ImBundle\Manager;
use Leapt\ImBundle\Twig\Extension\ImExtension;
use Leapt\ImBundle\Wrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class ImExtensionTest extends TestCase
{
    private ImExtension $imExtension;

    protected function setUp(): void
    {
        $this->imExtension = new ImExtension(new Manager(new Wrapper(Process::class), 'app/', '../web/', 'cache/im'));
    }

    /**
     * @dataProvider providerConvert
     */
    public function testConvert(string $input, string $expected): void
    {
        $this->assertEquals($expected, $this->imExtension->convert($input));
    }

    /**
     * @return iterable<array<string>>
     */
    public function providerConvert(): iterable
    {
        return [
            ['hop hop', 'hop hop'],
            ['<img src="/img.jpg"/>', '<img src="/img.jpg"/>'],
            ['hop <img src="/img.jpg" />hop', 'hop <img src="/img.jpg" />hop'],
            ['hop <img src="/img.jpg" width=""/>hop', 'hop <img src="/img.jpg" width=""/>hop'],
            ['hop <img src="/img.jpg" width="100" />hop', 'hop <img src="/cache/im/100x/img.jpg" width="100" />hop'],
            ['hop <img src="/path/img.jpg" height="120"/>hop', 'hop <img src="/cache/im/x120/path/img.jpg" height="120"/>hop'],
            ['hop <img src="/path/img.jpg" width="100" height="120" />hop', 'hop <img src="/cache/im/100x120/path/img.jpg" width="100" height="120" />hop'],
            ['hop <img height="100" src="/path/img.jpg"  width="120" data="content" />hop', 'hop <img height="100" src="/cache/im/120x100/path/img.jpg"  width="120" data="content" />hop'],
            ['hop <img height="100" src="/path/img.jpg"  width="120" data="path/img.jpg" />hop', 'hop <img height="100" src="/cache/im/120x100/path/img.jpg"  width="120" data="path/img.jpg" />hop'],
            ['hop <img src="/img.jpg" width="100" />hop <img src="/img2.jpg" width="100" /> hip', 'hop <img src="/cache/im/100x/img.jpg" width="100" />hop <img src="/cache/im/100x/img2.jpg" width="100" /> hip'],
            ['hop <img src="/img.jpg" width="100" />hop <img src="/img.jpg" width="120" /> hip', 'hop <img src="/cache/im/100x/img.jpg" width="100" />hop <img src="/cache/im/120x/img.jpg" width="120" /> hip'],
            ['hop <img src="/img.jpg" width="100" />hop <img src="/img.jpg" width="100" /> hip', 'hop <img src="/cache/im/100x/img.jpg" width="100" />hop <img src="/cache/im/100x/img.jpg" width="100" /> hip'],
        ];
    }

    /**
     * @dataProvider providerImResize
     */
    public function testImResize(string $filePath, string $format, string $expectedUrl): void
    {
        $this->assertEquals($expectedUrl, $this->imExtension->imResize($filePath, $format));
    }

    /**
     * @return iterable<array<string>>
     */
    public function providerImResize(): iterable
    {
        return [
            ['img.jpg', '100x', 'cache/im/100x/img.jpg'],
            ['/img.jpg', '100x', '/cache/im/100x/img.jpg'],
            ['/img.png', '100x', '/cache/im/100x/img.png'],
            ['/img.gif', '100x', '/cache/im/100x/img.gif'],
            ['/img.tiff', 'x100', '/cache/im/x100/img.tiff'],
            ['/img.jpg', 'x100', '/cache/im/x100/img.jpg'],
            ['/path/img.jpg', 'x100', '/cache/im/x100/path/img.jpg'],
            ['/path/img.jpg', '120x100', '/cache/im/120x100/path/img.jpg'],
            ['http://domain.tld/path/img.jpg', '120x100', 'cache/im/120x100/http/domain.tld/path/img.jpg'],
            ['https://domain.tld/path/img.jpg', '120x100', 'cache/im/120x100/https/domain.tld/path/img.jpg'],
        ];
    }
}
