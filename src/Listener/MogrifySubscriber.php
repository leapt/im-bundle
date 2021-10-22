<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Listener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Leapt\ImBundle\Doctrine\Mapping\Mogrify;
use Leapt\ImBundle\Manager as ImManager;

/**
 * Event listener for Doctrine entities to evaluate and execute ImBundle annotations.
 */
class MogrifySubscriber implements EventSubscriber
{
    private array $config = [];

    public function __construct(private ImManager $imManager)
    {
    }

    public function getSubscribedEvents(): array
    {
        return ['prePersist', 'preFlush'];
    }

    public function preFlush(PreFlushEventArgs $ea): void
    {
        $entityManager = $ea->getEntityManager();

        $unitOfWork = $entityManager->getUnitOfWork();

        $entityMaps = $unitOfWork->getIdentityMap();
        foreach ($entityMaps as $entities) {
            foreach ($entities as $entity) {
                foreach ($this->getFiles($entity, $ea->getEntityManager()) as $file) {
                    $this->mogrify($entity, $file);
                }
            }
        }
    }

    public function prePersist(LifecycleEventArgs $ea): void
    {
        $entity = $ea->getEntity();
        foreach ($this->getFiles($entity, $ea->getEntityManager()) as $file) {
            $this->mogrify($entity, $file);
        }
    }

    private function getFiles(object $entity, EntityManager $entityManager): array
    {
        $class = \get_class($entity);
        $this->checkClassConfig($entity, $entityManager);

        if (\array_key_exists($class, $this->config)) {
            return $this->config[$class]['fields'];
        }

        return [];
    }

    private function checkClassConfig(object $entity, EntityManager $entityManager): void
    {
        $class = \get_class($entity);

        if (!\array_key_exists($class, $this->config)) {
            $reader = new AnnotationReader();
            $meta = $entityManager->getClassMetaData($class);
            $reflexionClass = $meta->getReflectionClass();
            if (null !== $reflexionClass) {
                foreach ($reflexionClass->getProperties() as $property) {
                    if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                        $meta->isInheritedField($property->name) ||
                        isset($meta->associationMappings[$property->name]['inherited'])
                    ) {
                        continue;
                    }
                    /** @var $annotation \Leapt\ImBundle\Doctrine\Mapping\Mogrify */
                    if ($annotation = $reader->getPropertyAnnotation($property, Mogrify::class)) {
                        $field = $property->getName();
                        $this->config[$class]['fields'][$field] = [
                            'property' => $property,
                            'params'   => $annotation->params,
                        ];
                    }
                }
            }
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
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
            $uploadedFile = $entity->$getter();
            if (null !== $uploadedFile) {
                $this->imManager->mogrify($file['params'], $uploadedFile->getPathName());
            }
        }
    }
}
