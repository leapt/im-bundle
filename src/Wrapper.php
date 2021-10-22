<?php

declare(strict_types=1);

namespace Leapt\ImBundle;

use Leapt\ImBundle\Exception\InvalidArgumentException;
use Leapt\ImBundle\Exception\RuntimeException;

class Wrapper
{
    private const ACCEPTED_BINARIES = [
        'animate', 'compare', 'composite',
        'conjure', 'convert', 'display',
        'identify', 'import', 'mogrify',
        'montage', 'stream',
    ];

    private string $binaryPath;

    /**
     * @param string $processClass The class name of the command line processor
     * @param string $binaryPath   The path where the Imagemagick binaries lies
     * @param int    $timeout      The timeout in seconds
     */
    public function __construct(
        private string $processClass,
        string $binaryPath = '',
        private int $timeout = 60,
    ) {
        $this->binaryPath = empty($binaryPath) ? $binaryPath : rtrim($binaryPath, '/') . '/';
    }

    public function run(string $command, string $inputFile, array $attributes = [], string $outputFile = ''): string
    {
        $commandString = $this->buildCommand($command, $inputFile, $attributes, $outputFile);

        return $this->rawRun($commandString);
    }

    /**
     * Run a command. Only use if the run() method doesn't fit your need.
     */
    public function rawRun(string $commandString): string
    {
        $this->validateCommand($commandString);

        /** @var \Symfony\Component\Process\Process $process */
        $process = $this->processClass::fromShellCommandline($commandString);
        $process->setTimeout($this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Creates the given directory if does not exist.
     */
    public function checkDirectory(string $path): void
    {
        $dir = \dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Unable to create the "%s" directory', $dir));
        }
    }

    private function buildCommand(string $command, string $inputFile, array $attributes = [], string $outputFile = ''): string
    {
        $attributesString = trim($this->prepareAttributes($attributes));
        if ('' !== $attributesString) {
            $attributesString = ' ' . $attributesString;
        }

        if ('' !== $outputFile) {
            $this->checkDirectory($outputFile);

            $commandString = $this->binaryPath . $command . ' ' . $inputFile . $attributesString . ' ' . $outputFile;
        } else {
            $commandString = $this->binaryPath . $command . $attributesString . ' ' . $inputFile;
        }

        $this->validateCommand($commandString);

        return $commandString;
    }

    /**
     * Takes an array of attributes and formats it as CLI parameters.
     */
    private function prepareAttributes(array $attributes = []): string
    {
        $result = '';

        foreach ($attributes as $key => $value) {
            if (null === $key || '' === $key) {
                $result .= ' ' . $value;
            } else {
                $result .= ' -' . $key;
                if ('' !== $value) {
                    $result .= ' "' . $value . '"';
                }
            }
        }

        return $result;
    }

    /**
     * Validates that the command launches a Imagemagick command line tool executable.
     */
    private function validateCommand(string $commandString): bool
    {
        $cmdParts = explode(' ', $commandString);

        if (2 > \count($cmdParts)) {
            throw new InvalidArgumentException("This command isn't properly structured : '" . $commandString . "'");
        }

        $binaryPath = $cmdParts[0];
        $binaryPathParts = explode('/', $binaryPath);
        $binary = $binaryPathParts[\count($binaryPathParts) - 1];

        if (!\in_array($binary, self::ACCEPTED_BINARIES, true)) {
            throw new InvalidArgumentException("This command isn't part of the ImageMagick command line tools : '" . $binary . "'");
        }

        return true;
    }
}
