<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\Command;

use Leapt\ImBundle\Command\ClearCommand;
use Leapt\ImBundle\Manager;
use Leapt\ImBundle\Tests\Mock\Process;
use Leapt\ImBundle\Wrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

final class ClearCommandTest extends TestCase
{
    private Filesystem $filesystem;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        // the space and single quote purposely make sure the "find" shell command used
        // internally to prune empty directories properly escapes the cache path
        $this->tmpDir = sys_get_temp_dir() . '/leapt-im-bundle-' . uniqid() . " o'clock";
        $this->filesystem->mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tmpDir);
    }

    public function testClearAllCache(): void
    {
        $cacheDir = $this->tmpDir . '/public/cache/im';
        $this->filesystem->dumpFile($cacheDir . '/100x100/img.jpg', 'content');

        $tester = new CommandTester(new ClearCommand($this->createManager()));
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFalse($this->filesystem->exists($cacheDir));
    }

    public function testClearCacheOlderThanAgePrunesEmptyDirectories(): void
    {
        $cacheDir = $this->tmpDir . '/public/cache/im';
        $oldFile = $cacheDir . '/100x100/old.jpg';
        $recentFile = $cacheDir . '/200x200/recent.jpg';

        $this->filesystem->dumpFile($oldFile, 'content');
        touch($oldFile, strtotime('-10 days'));
        $this->filesystem->dumpFile($recentFile, 'content');

        $tester = new CommandTester(new ClearCommand($this->createManager()));
        $tester->execute(['age' => 5]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFalse($this->filesystem->exists($cacheDir . '/100x100'), 'the directory left empty after clearing should be pruned');
        $this->assertTrue($this->filesystem->exists($recentFile), 'files younger than the given age must be kept');
    }

    private function createManager(): Manager
    {
        return new Manager(new Wrapper(Process::class), $this->tmpDir, 'public', 'cache/im');
    }
}
