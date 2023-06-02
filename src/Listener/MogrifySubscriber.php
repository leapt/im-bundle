<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Leapt\ImBundle\Doctrine\Mapping\Mogrify;
use Leapt\ImBundle\Manager as ImManager;

/**
 * Event listener for Doctrine entities to evaluate and execute ImBundle attributes.
 */
class MogrifySubscriber
{
    private array $config = [];

    public function __construct(private ImManager $imManager)
    {
    }

    public function preFlush(PreFlushEventArgs $ea): void
    {
        $entityManager = $ea->getObjectManager();

        $unitOfWork = $entityManager->getUnitOfWork();

        $entityMaps = $unitOfWork->getIdentityMap();
        foreach ($entityMaps as $entities) {
            foreach ($entities as $entity) {
                foreach ($this->getFiles($entity, $ea->getObjectManager()) as $file) {
                    $this->mogrify($entity, $file);
                }
            }
        }
    }

    public function prePersist(PrePersistEventArgs $ea): void
    {
        $entity = $ea->getObject();
        foreach ($this->getFiles($entity, $ea->getObjectManager()) as $file) {
            $this->mogrify($entity, $file);
        }
    }

    private function getFiles(object $entity, EntityManagerInterface $entityManager): array
    {
        $class = $entity::class;
        $this->checkClassConfig($entity, $entityManager);

        if (\array_key_exists($class, $this->config)) {
            return $this->config[$class]['fields'];
        }

        return [];
    }

    private function checkClassConfig(object $entity, EntityManagerInterface $entityManager): void
    {
        $class = $entity::class;

        if (!\array_key_exists($class, $this->config)) {
            $meta = $entityManager->getClassMetaData($class);

            foreach ($meta->getReflectionClass()->getProperties() as $property) {
                if ($meta->isMappedSuperclass && !$property->isPrivate()
                    || $meta->isInheritedField($property->name)
                    || isset($meta->associationMappings[$property->name]['inherited'])
                ) {
                    continue;
                }
                $attributes = $this->getAttributes($property);
                foreach ($attributes as $attribute) {
                    $field = $property->getName();
                    $this->config[$class]['fields'][$field] = [
                        'property' => $property,
                        'params'   => $attribute->params,
                    ];
                }
            }
        }
    }

    /**
     * @return iterable<Mogrify>
     */
    private function getAttributes(\ReflectionProperty $reflection): iterable
    {
        foreach ($reflection->getAttributes(Mogrify::class) as $attribute) {
            yield $attribute->newInstance();
        }
    }

    /**
     * @param array<string|\ReflectionProperty> $file
     */
    private function mogrify(object $entity, array $file): void
    {
        $propertyName = $file['property']->name;

        $getter = 'get' . ucfirst($propertyName);
        if (method_exists($entity, $getter)) {
            $uploadedFile = $entity->$getter();
            if (null !== $uploadedFile) {
                /* @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
                $this->imManager->mogrify($file['params'], $uploadedFile->getPathName());
            }
        }
    }
}
