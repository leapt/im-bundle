<?php

namespace Leapt\ImBundle\Tests\Mock;

use Symfony\Component\Process\Process as BaseProcess;

/**
 * Mock object for the process class.
 */
class Process extends BaseProcess
{
    private $cmd;
    private $success;

    public function __construct(array $command)
    {
        $this->cmd = $command[0];
    }

    public static function fromShellCommandline(string $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60): static
    {
        return new self([$command]);
    }

    /**
     * Run the process.
     */
    public function run(callable $callback = null, array $env = []): int
    {
        if ('mogrify "somefailingstructure' === $this->cmd) {
            $this->success = false;

            return 255;
        }
        $this->success = true;

        return 0;
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function getOutput(): string
    {
        return 'output';
    }

    public function getErrorOutput(): string
    {
        return 'errormsg';
    }
}
