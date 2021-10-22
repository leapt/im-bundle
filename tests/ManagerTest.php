<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests;

use Leapt\ImBundle\Exception\InvalidArgumentException;
use Leapt\ImBundle\Exception\NotFoundException;
use Leapt\ImBundle\Manager;
use Leapt\ImBundle\Tests\Mock\Process;
use Leapt\ImBundle\Wrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

final class ManagerTest extends TestCase
{
    private string $projectDir;
    private string $publicPath;
    private string $cachePath;
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('/root');
        $this->projectDir = 'vfs://app';
        $this->publicPath = '../public';
        $this->cachePath = 'cache/im';
    }

    public function testConstruct(): Manager
    {
        $formats = [
            'list' => ['resize' => '100x100'],
        ];

        $wrapper = new Wrapper(Process::class);
        $manager = new Manager($wrapper, $this->projectDir, $this->publicPath, $this->cachePath, $formats);

        $this->assertEquals($wrapper, $this->getManagerPrivateValue('wrapper', $manager));
        $this->assertEquals($formats, $this->getManagerPrivateValue('formats', $manager));
        $this->assertEquals($this->projectDir, $manager->getProjectDir());
        $this->assertEquals($this->publicPath, $manager->getPublicPath());
        $this->assertEquals($this->cachePath, $manager->getCachePath());
        $this->assertEquals($this->projectDir . '/' . trim($this->publicPath, '/') . '/' . $this->cachePath, $manager->getCacheDirectory());

        return $manager;
    }

    /**
     * @depends testConstruct
     */
    public function testSetCachePath(Manager $manager): void
    {
        $manager->setCachePath('somepath');

        $this->assertEquals('somepath', $manager->getCachePath());
        $this->assertEquals($this->projectDir . '/../public/somepath', $manager->getCacheDirectory());

        $manager->setCachePath($this->cachePath);
    }

    /**
     * @depends testConstruct
     */
    public function testCacheExists(Manager $manager): void
    {
        $this->markTestSkipped();
        $this->root = vfsStream::setup('/root');
        $filepath = 'somefile';
        $format = '50x';
        $this->assertFalse($manager->cacheExists($format, $filepath));

        $structure = [
            'app'    => [],
            'public' => [
                'cache' => [
                    'im' => [
                        $format => [$filepath => 'somecontent'],
                    ],
                ],
            ],
        ];
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $this->assertTrue($manager->cacheExists($format, $filepath));
    }

    /**
     * @depends testConstruct
     */
    public function testGetCacheContent(Manager $manager): void
    {
        $this->markTestSkipped();
        $structure = [
            'app'    => [],
            'public' => [
                'cache' => [
                    'im' => [
                        'format' => ['somefile' => 'somecontent'],
                    ],
                ],
            ],
        ];
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $this->assertEquals('somecontent', $manager->getCacheContent('format', 'somefile'));
    }

    /**
     * @depends testConstruct
     */
    public function testGetUrl(Manager $manager): void
    {
        $format = 'someformat';
        $path = 'somepath';

        $this->assertEquals($this->cachePath . '/' . $format . '/' . $path, $manager->getUrl($format, $path));
        $manager->setCachePath('somepath/');
        $this->assertEquals('somepath/' . $format . '/' . $path, $manager->getUrl($format, $path));
        $manager->setCachePath($this->cachePath);
    }

    /**
     * @depends testConstruct
     */
    public function testConvertFormat(Manager $manager): void
    {
        $method = new \ReflectionMethod($manager, 'convertFormat');
        $method->setAccessible(true);

        $this->assertEquals(['resize' => '100x100'], $method->invoke($manager, 'list'));
        $this->assertEquals(['resize' => '100x100', 'crop' => '50x50+1+1'], $method->invoke($manager, ['resize' => '100x100', 'crop' => '50x50+1+1']));
        $this->assertEquals(['thumbnail' => '100x100'], $method->invoke($manager, '100x100'));
        $this->assertEquals(['thumbnail' => '100x'], $method->invoke($manager, '100x'));
        $this->assertEquals(['thumbnail' => 'x100'], $method->invoke($manager, 'x100'));
    }

    /**
     * @depends testConstruct
     */
    public function testConvertFormatException(Manager $manager): void
    {
        $this->expectException(InvalidArgumentException::class);

        $method = new \ReflectionMethod($manager, 'convertFormat');
        $method->setAccessible(true);

        $method->invoke($manager, 'someunknownformat');
    }

    /**
     * @depends testConstruct
     */
    public function testCheckImage(Manager $manager): void
    {
        $this->markTestSkipped();
        $structure = [
            'app'    => [],
            'public' => [
                'uploads' => [
                    'somefile' => 'somecontent',
                ],
            ],
        ];
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $method = new \ReflectionMethod($manager, 'checkImage');
        $method->setAccessible(true);

        $method->invoke($manager, 'uploads/somefile');
        $method->invoke($manager, 'vfs://public/uploads/somefile');
        $this->assertTrue(true);
    }

    /**
     * @depends testConstruct
     */
    public function testCheckImageException(Manager $manager): void
    {
        $this->expectException(NotFoundException::class);

        $method = new \ReflectionMethod($manager, 'checkImage');
        $method->setAccessible(true);

        $method->invoke($manager, 'someinexistantfile');
    }

    /**
     * @depends testConstruct
     */
    public function testPathify(Manager $manager): void
    {
        $method = new \ReflectionMethod($manager, 'pathify');
        $method->setAccessible(true);

        $simplePath = $method->invoke($manager, '200x150');
        $this->assertIsString($simplePath);

        $path = $method->invoke($manager, ['crop' => '100x100']);
        $this->assertIsString($path);

        $otherPath = $method->invoke($manager, ['crop' => '100x100+10']);
        $this->assertIsString($otherPath);

        $this->assertNotEquals($simplePath, $path);
        $this->assertNotEquals($path, $otherPath);
    }

    private function getManagerPrivateValue(string $propertyName, Manager $manager): mixed
    {
        $reflection = new \ReflectionClass($manager);

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($manager);
    }
}
