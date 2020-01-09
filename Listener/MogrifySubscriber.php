<?php

namespace Leapt\ImBundle\Listener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Leapt\ImBundle\Manager as ImManager;

/**
 * Event listener for Doctrine entities to evualuate and execute ImBundle annotations.
 */
class MogrifySubscriber implements EventSubscriber
{
    private $config = [];

    /**
     * @var \Leapt\ImBundle\Manager
     */
    private $imManager;

    public function __construct(ImManager $imManager)
    {
        $this->imManager = $imManager;
    }

    public function getSubscribedEvents()
    {
        return ['prePersist', 'preFlush'];
    }

    /**
     * @param PreFlushEventArgs $ea
     */
    public function preFlush(PreFlushEventArgs $ea)
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

    /**
     * @param LifecycleEventArgs $ea
     */
    public function prePersist(LifecycleEventArgs $ea)
    {
        $entity = $ea->getEntity();
        foreach ($this->getFiles($entity, $ea->getEntityManager()) as $file) {
            $this->mogrify($entity, $file);
        }
    }

    /**
     * @param $entity
     * @param EntityManager $entityManager
     * @return array
     */
    private function getFiles($entity, EntityManager $entityManager)
    {
        $class = get_class($entity);
        $this->checkClassConfig($entity, $entityManager);

        if (array_key_exists($class, $this->config)) {
            return $this->config[$class]['fields'];
        } else {
            return [];
        }
    }

    private function checkClassConfig($entity, EntityManager $entityManager)
    {
        $class = get_class($entity);

        if (!array_key_exists($class, $this->config)) {
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
                    if ($annotation = $reader->getPropertyAnnotation($property, 'Leapt\\ImBundle\\Doctrine\\Mapping\\Mogrify')) {
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
     * @param $entity
     * @param $file
     */
    private function mogrify($entity, $file)
    {
        $propertyName = $file['property']->name;

        $getter = 'get' . ucFirst($propertyName);
        if (method_exists($entity, $getter)) {
            /** @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
            $uploadedFile = $entity->$getter();
            if (null !== $uploadedFile) {
                $this->imManager->mogrify($file['params'], $uploadedFile->getPathName());
            }
        }
    }
}
