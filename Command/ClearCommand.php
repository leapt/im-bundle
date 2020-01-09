<?php

namespace Leapt\ImBundle\Command;

use Leapt\ImBundle\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Command line task to clear (remove) generated files
 */
class ClearCommand extends Command
{
    /**
     * @var Manager
     */
    private $imManager;

    public function __construct(Manager $imManager)
    {
        $this->imManager = $imManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('leapt:im:clear')
            ->setDescription('Clear IM cache')
            ->addArgument('age', InputArgument::OPTIONAL, 'Clear only files older than (days)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDir = $this->imManager->getCacheDirectory();
        $filesystem = new Filesystem();

        $age = $input->getArgument('age');
        if ($age) {

            $output->writeln(sprintf('Clearing the IM cache older than %s days', $age));

            $finder = new Finder();
            foreach ($finder->in($cacheDir)->files()->date('until ' . $age . ' days ago') as $file) {
                $filesystem->remove($file);
            }

            // removing empty directories
            $process = new Process("find " . $cacheDir . " -type d -empty");
            $process->run();
            $emptyDirectories = explode("\n", $process->getOutput());
            foreach ($emptyDirectories as $directory) {
                if ($directory != "." && $directory != ".." && $directory != "") {
                    $filesystem->remove($directory);
                }
            }

        } else {

            $output->writeln('Clearing all the IM cache');

            $filesystem->remove($cacheDir);
        }

        return 0;
    }
}
