<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\Mock;

use Symfony\Component\Process\Process as BaseProcess;

final class Process extends BaseProcess
{
    private string $cmd;
    private bool $success;

    /**
     * @param array<string> $command
     */
    public function __construct(array $command)
    {
        $this->cmd = $command[0];
    }

    /**
     * @param array<string>|null $env
     */
    public static function fromShellCommandline(string $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60): static
    {
        return new self([$command]);
    }

    /**
     * @param array<string> $env
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
