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

    /**
     * @param string $cmd
     * @param null   $cwd
     * @param array  $env
     * @param null   $input
     * @param int    $timeout
     */
    public function __construct($cmd, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = [])
    {
        $this->cmd = $cmd;
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

    /**
     * @return mixed
     */
    public function isSuccessful()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return 'output';
    }

    /**
     * @return string
     */
    public function getErrorOutput()
    {
        return 'errormsg';
    }
}
