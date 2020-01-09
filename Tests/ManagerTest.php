<?php

namespace Leapt\ImBundle\Tests;

use Leapt\ImBundle\Exception\InvalidArgumentException;
use Leapt\ImBundle\Exception\NotFoundException;
use Leapt\ImBundle\Manager;
use Leapt\ImBundle\Wrapper;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Manager tester class
 */
class ManagerTest extends TestCase
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var string
     */
    private $publicPath;

    /**
     * @var string
     */
    private $cachePath;

    /**
     * @var  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * Initializing the vfsStream stream wrapper
     */
    public function setUp(): void
    {
        $this->root = vfsStream::setup("/root");
        $this->projectDir = "vfs://app";
        $this->publicPath = "../public";
        $this->cachePath = "cache/im";
    }

    /**
     * @return \Leapt\ImBundle\Manager
     */
    public function test__construct()
    {
        $formats = array(
            'list' => array('resize' => '100x100')
        );

        $wrapper = new Wrapper('\Leapt\ImBundle\Tests\Mock\Process');
        $manager = new Manager($wrapper, $this->projectDir, $this->publicPath, $this->cachePath, $formats);

        $this->assertEquals($wrapper, $this->getManagerPrivateValue('wrapper', $manager));
        $this->assertEquals($formats, $this->getManagerPrivateValue('formats', $manager));
        $this->assertEquals($this->projectDir, $manager->getProjectDir());
        $this->assertEquals($this->publicPath, $manager->getPublicPath());
        $this->assertEquals($this->cachePath, $manager->getCachePath());
        $this->assertEquals($this->projectDir . '/'. trim($this->publicPath, '/') . '/' . $this->cachePath, $manager->getCacheDirectory());

        return $manager;
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testSetCachePath(Manager $manager)
    {
        $manager->setCachePath('somepath');

        $this->assertEquals('somepath', $manager->getCachePath());
        $this->assertEquals($this->projectDir . '/../public/somepath', $manager->getCacheDirectory());

        $manager->setCachePath($this->cachePath);
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testCacheExists(Manager $manager)
    {
        $this->root = vfsStream::setup("/root");
        $filepath = "somefile";
        $format = "50x";
        $this->assertFalse($manager->cacheExists($format, $filepath));

        $structure = array(
            "app" => array(),
            "public" => array(
                "cache" => array(
                    "im" => array(
                        $format => array($filepath => 'somecontent')
                    )
                )
            )
        );
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $this->assertTrue($manager->cacheExists($format, $filepath));
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testGetCacheContent(Manager $manager)
    {
        $structure = array(
            "app" => array(),
            "public" => array(
                "cache" => array(
                    "im" => array(
                        'format' => array('somefile' => 'somecontent')
                    )
                )
            )
        );
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $this->assertEquals('somecontent', $manager->getCacheContent('format', 'somefile'));
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testGetUrl(Manager $manager)
    {
        $format = 'someformat';
        $path = 'somepath';

        $this->assertEquals($this->cachePath . '/' . $format . '/' . $path, $manager->getUrl($format, $path));
        $manager->setCachePath('somepath/');
        $this->assertEquals('somepath/' . $format . '/' . $path, $manager->getUrl($format, $path));
        $manager->setCachePath($this->cachePath);
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testConvertFormat(Manager $manager)
    {
        $method = new \ReflectionMethod($manager, 'convertFormat');
        $method->setAccessible(true);

        $this->assertEquals(array('resize' => '100x100'), $method->invoke($manager, 'list'));
        $this->assertEquals(array('resize' => '100x100', 'crop' => '50x50+1+1'), $method->invoke($manager, array('resize' => '100x100', 'crop' => '50x50+1+1')));
        $this->assertEquals(array('thumbnail' => '100x100'), $method->invoke($manager, '100x100'));
        $this->assertEquals(array('thumbnail' => '100x'), $method->invoke($manager, '100x'));
        $this->assertEquals(array('thumbnail' => 'x100'), $method->invoke($manager, 'x100'));
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testConvertFormatException(Manager $manager)
    {
        $this->expectException(InvalidArgumentException::class);

        $method = new \ReflectionMethod($manager, 'convertFormat');
        $method->setAccessible(true);

        $method->invoke($manager, 'someunknownformat');
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testCheckImage(Manager $manager)
    {
        $structure = array(
            "app" => array(),
            "public" => array(
                "uploads" => array(
                    'somefile' => 'somecontent'
                )
            )
        );
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $method = new \ReflectionMethod($manager, 'checkImage');
        $method->setAccessible(true);

        $method->invoke($manager, 'uploads/somefile');
        $method->invoke($manager, 'vfs://public/uploads/somefile');
        $this->assertTrue(true);
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testCheckImageException(Manager $manager)
    {
        $this->expectException(NotFoundException::class);

        $method = new \ReflectionMethod($manager, 'checkImage');
        $method->setAccessible(true);

        $method->invoke($manager, 'someinexistantfile');
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testPathify(Manager $manager)
    {
        $method = new \ReflectionMethod($manager, 'pathify');
        $method->setAccessible(true);

        $simplePath = $method->invoke($manager, '200x150');
        $this->assertTrue(is_string($simplePath));

        $path = $method->invoke($manager, array('crop' => '100x100'));
        $this->assertTrue(is_string($path));

        $otherPath = $method->invoke($manager, array('crop' => '100x100+10'));
        $this->assertTrue(is_string($otherPath));

        $this->assertNotEquals($simplePath, $path);
        $this->assertNotEquals($path, $otherPath);
    }

    /**
     * @param Manager $manager
     *
     * @return \ReflectionClass
     */
    private function getManagerReflection(Manager $manager)
    {
        return new \ReflectionClass($manager);
    }

    /**
     * @param string  $propertyName The name of the private property
     * @param Manager $manager      The manager instance
     *
     * @return mixed
     */
    private function getManagerPrivateValue($propertyName, Manager $manager)
    {
        $reflection = $this->getManagerReflection($manager);

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($manager);
    }
}
