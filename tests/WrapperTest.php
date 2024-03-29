<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests;

use Leapt\ImBundle\Exception\InvalidArgumentException;
use Leapt\ImBundle\Exception\RuntimeException;
use Leapt\ImBundle\Tests\Mock\Process;
use Leapt\ImBundle\Wrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @param array<string|array<string|null, string>> $attributes
     */
    #[DataProvider('providerPrepareAttributes')]
    public function testPrepareAttributes(array $attributes, string $expected): void
    {
        $method = new \ReflectionMethod($this->wrapper, 'prepareAttributes');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->wrapper, $attributes));
    }

    /**
     * @return iterable<array<string|array<string|null, string>>>
     */
    public static function providerPrepareAttributes(): iterable
    {
        yield 'empty_config' => [
            [],
            '',
        ];
        yield 'simple_string' => [
            [
                'resize' => '150x150^',
            ],
            ' -resize "150x150^"',
        ];
        yield 'array_config' => [
            [
                'resize' => '120x',
                null     => '+opaque -transparent',
            ],
            ' -resize "120x" +opaque -transparent',
        ];
    }

    #[DataProvider('providerPrepareAttributesException')]
    public function testPrepareAttributesException(mixed $attributes): void
    {
        $this->expectException(\TypeError::class);
        $method = new \ReflectionMethod($this->wrapper, 'prepareAttributes');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $attributes);
    }

    /**
     * @return iterable<array<string|object>>
     */
    public static function providerPrepareAttributesException(): iterable
    {
        yield ['some crappy string'];
        yield [new \stdClass()];
    }

    /**
     * @param array<string|array<string, string>> $attributes
     */
    #[DataProvider('providerBuildCommand')]
    public function testBuildCommand(string $command, string $inputFile, array $attributes, string $outputFile, string $expected): void
    {
        $method = new \ReflectionMethod($this->wrapper, 'buildCommand');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->wrapper, $command, $inputFile, $attributes, $outputFile));
    }

    /**
     * @return iterable<array<string|array<string, string>>>
     */
    public static function providerBuildCommand(): iterable
    {
        yield ['convert', 'somefile', [], 'anotherfile', 'convert somefile anotherfile'];
        yield ['mogrify', 'somefile', ['resize' => '450x'], '', 'mogrify -resize "450x" somefile'];
        yield ['montage', 'somefile', ['resize' => '450x'], '', 'montage -resize "450x" somefile'];
    }

    /**
     * @param array<string|array<string>> $attributes
     */
    #[DataProvider('providerBuildCommandException')]
    public function testBuildCommandException(string $command, string $inputFile, array $attributes, string $outputFile): void
    {
        $this->expectException(InvalidArgumentException::class);

        $method = new \ReflectionMethod($this->wrapper, 'buildCommand');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $command, $inputFile, $attributes, $outputFile);
    }

    /**
     * @return iterable<array<string|array<string>>>
     */
    public static function providerBuildCommandException(): iterable
    {
        yield ['ls', 'somefile', [], 'anotherfile'];
        yield ['blaarhh', '', [], ''];
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

    #[DataProvider('providerValidateCommand')]
    public function testValidateCommand(string $commandString): void
    {
        $method = new \ReflectionMethod($this->wrapper, 'validateCommand');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->wrapper, $commandString));
    }

    /**
     * @return iterable<array<string>>
     */
    public static function providerValidateCommand(): iterable
    {
        yield ['convert somestrings'];
        yield ['mogrify somestrings blouh +yop -paf -bim "zoup"'];
    }

    #[DataProvider('providerValidateCommandException')]
    public function testValidateCommandException(string $commandString): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $method = new \ReflectionMethod($this->wrapper, 'validateCommand');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $commandString);
    }

    /**
     * @return iterable<array<string>>
     */
    public static function providerValidateCommandException(): iterable
    {
        yield ['convert'];
        yield ['bignou'];
        yield ['bignou didjou'];
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

        \assert(vfsStreamWrapper::getRoot() instanceof vfsStreamContent);
        vfsStreamWrapper::getRoot()->chmod(0400);
        $method->invoke($this->wrapper, vfsStream::url('exampleDir/mypath/.'));
    }
}
