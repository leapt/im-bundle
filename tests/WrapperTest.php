<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests;

use Leapt\ImBundle\Exception\InvalidArgumentException;
use Leapt\ImBundle\Exception\RuntimeException;
use Leapt\ImBundle\Tests\Mock\Process;
use Leapt\ImBundle\Wrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

final class WrapperTest extends TestCase
{
    private Wrapper $wrapper;
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->wrapper = new Wrapper(Process::class);
        $this->root = vfsStream::setup('exampleDir');
    }

    /**
     * @dataProvider providerPrepareAttributes
     */
    public function testPrepareAttributes(array $attributes, string $expected): void
    {
        $method = new \ReflectionMethod($this->wrapper, 'prepareAttributes');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->wrapper, $attributes));
    }

    public function providerPrepareAttributes(): iterable
    {
        return [
            [
                [],
                '',
            ],
            [
                [
                    'resize' => '150x150^',
                ],
                ' -resize "150x150^"',
            ],
            [
                [
                    'resize' => '120x',
                    null     => '+opaque -transparent',
                ],
                ' -resize "120x" +opaque -transparent',
            ],
        ];
    }

    /**
     * @dataProvider providerPrepareAttributesException
     */
    public function testPrepareAttributesException(mixed $attributes): void
    {
        $this->expectException(InvalidArgumentException::class);
        $method = new \ReflectionMethod($this->wrapper, 'prepareAttributes');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $attributes);
    }

    public function providerPrepareAttributesException(): iterable
    {
        return [
            ['some crappy string'],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider providerBuildCommand
     */
    public function testBuildCommand(string $command, string $inputFile, array $attributes, string $outputFile, string $expected): void
    {
        $method = new \ReflectionMethod($this->wrapper, 'buildCommand');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->wrapper, $command, $inputFile, $attributes, $outputFile));
    }

    public function providerBuildCommand(): iterable
    {
        return [
            ['convert', 'somefile', [], 'anotherfile', 'convert somefile anotherfile'],
            ['mogrify', 'somefile', ['resize' => '450x'], '', 'mogrify -resize "450x" somefile'],
            ['montage', 'somefile', ['resize' => '450x'], '', 'montage -resize "450x" somefile'],
        ];
    }

    /**
     * @dataProvider providerBuildCommandException
     */
    public function testBuildCommandException(string $command, string $inputFile, array $attributes, string $outputFile): void
    {
        $this->expectException(InvalidArgumentException::class);

        $method = new \ReflectionMethod($this->wrapper, 'buildCommand');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $command, $inputFile, $attributes, $outputFile);
    }

    public function providerBuildCommandException(): iterable
    {
        return [
            ['ls', 'somefile', [], 'anotherfile'],
            ['blaarhh', '', [], ''],
        ];
    }

    public function testRawRun(): void
    {
        $this->assertEquals('output', $this->wrapper->rawRun('mogrify -resize 120x somefile'));
    }

    public function testRawRunInvalidException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->wrapper->rawRun('crap');
    }

    public function testRawRunRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->wrapper->rawRun('mogrify "somefailingstructure');
    }

    /**
     * @dataProvider providerValidateCommand
     */
    public function testValidateCommand(string $commandString): void
    {
        $method = new \ReflectionMethod($this->wrapper, 'validateCommand');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->wrapper, $commandString));
    }

    public function providerValidateCommand(): iterable
    {
        return [
            ['convert somestrings'],
            ['mogrify somestrings blouh +yop -paf -bim "zoup"'],
        ];
    }

    /**
     * @dataProvider providerValidateCommandException
     */
    public function testValidateCommandException(string $commandString): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $method = new \ReflectionMethod($this->wrapper, 'validateCommand');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $commandString);
    }

    public function providerValidateCommandException(): iterable
    {
        return [
            ['convert'],
            ['bignou'],
            ['bignou didjou'],
        ];
    }

    /**
     * Checking folder creation & retrieval.
     */
    public function testCheckDirectory(): void
    {
        $this->assertFalse($this->root->hasChild('mypath'));
        $this->wrapper->checkDirectory(vfsStream::url('exampleDir/mypath/.'));
        $this->assertTrue($this->root->hasChild('mypath'));
    }

    public function testCheckDirectoryException(): void
    {
        $this->expectException(RuntimeException::class);

        $method = new \ReflectionMethod($this->wrapper, 'checkDirectory');
        $method->setAccessible(true);

        vfsStreamWrapper::getRoot()->chmod(0400);
        $method->invoke($this->wrapper, vfsStream::url('exampleDir/mypath/.'));
    }
}
