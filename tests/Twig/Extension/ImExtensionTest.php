<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\Twig\Extension;

use Leapt\ImBundle\Manager;
use Leapt\ImBundle\Twig\Extension\ImExtension;
use Leapt\ImBundle\Wrapper;
use PHPUnit\Framework\TestCase;

/**
 * Wrapper tester class.
 */
class ImExtensionTest extends TestCase
{
    /** @var ImExtension */
    private $imExtension;

    protected function setUp(): void
    {
        $this->imExtension = new ImExtension(new Manager(new Wrapper('\Symfony\Component\Process\Process'), 'app/', '../web/', 'cache/im'));
    }

    /**
     * @param string $input    the string to parse
     * @param string $expected what we except as parsing in return
     *
     * @dataProvider providerConvert
     */
    public function testConvert($input, $expected)
    {
        $this->assertEquals($expected, $this->imExtension->convert($input));
    }

    /**
     * @return array
     */
    public function providerConvert()
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
     * @param string $path     File path
     * @param string $format   ImBundle format string
     * @param string $expected what we excpect as new url
     *
     * @dataProvider providerImResize
     */
    public function testImResize($path, $format, $expected)
    {
        $this->assertEquals($expected, $this->imExtension->imResize($path, $format));
    }

    /**
     * @return array
     */
    public function providerImResize()
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
