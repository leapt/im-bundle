<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\Listener;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Leapt\ImBundle\Listener\MogrifySubscriber;
use Leapt\ImBundle\Manager;
use Leapt\ImBundle\Tests\Listener\Fixtures\Entity\News;
use Leapt\ImBundle\Tests\Mock\Process;
use Leapt\ImBundle\Wrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

final class MogrifySubscriberTest extends TestCase
{
    private EntityManagerInterface $em;
    private MogrifySubscriber $subscriber;
    private string $rootDir;

    /**
     * @var array<class-string>
     */
    private array $classes = [
        News::class,
    ];

    protected function setUp(): void
    {
        $this->em = $this->buildEntityManager();
        $this->rootDir = sys_get_temp_dir() . '/' . uniqid('', false);
        $imManager = new Manager(new Wrapper(Process::class), __DIR__, '/public', '/cache');
        $this->subscriber = new MogrifySubscriber($imManager);
    }

    public function testPrePersist(): void
    {
        $this->expectNotToPerformAssertions();
        $object = new News();
        $object->setImage(new File($this->copyFile(__DIR__ . '/../fixtures/base-picture.jpg', '/base-picture.jpg')));
        $eventArgs = new PrePersistEventArgs($object, $this->em);
        $this->subscriber->prePersist($eventArgs);
    }

    private function buildEntityManager(): EntityManagerInterface
    {
        $config = ORMSetup::createConfiguration(false, sys_get_temp_dir());
        $config->setMetadataDriverImpl(new AttributeDriver([__DIR__ . '/Fixtures']));
        $config->setAutoGenerateProxyClasses(true);

        $params = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $em = new EntityManager(DriverManager::getConnection($params), $config);

        // Create schema
        $schema = array_map(static function ($class) use ($em) {
            return $em->getClassMetadata($class);
        }, $this->classes);
        \assert(array_is_list($schema));

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema($schema);

        return $em;
    }

    private function copyFile(string $from, string $to): string
    {
        $fs = new Filesystem();
        $targetPath = $this->rootDir . $to;
        $fs->copy($from, $targetPath);

        return $targetPath;
    }
}
