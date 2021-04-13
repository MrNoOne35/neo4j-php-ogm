<?php

/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Metadata\Factory;

use Doctrine\Common\Annotations\Reader;
use Neo4j\OGM\Annotation\Convert;
use Neo4j\OGM\Annotation\Entity;
use Neo4j\OGM\Annotation\Relationship;
use Neo4j\OGM\Exception\MappingException;
use Neo4j\OGM\Metadata\EntityAnnotationMetadata;
use Neo4j\OGM\Metadata\EntityMetadata;
use Neo4j\OGM\Metadata\IdMetadata;
use Neo4j\OGM\Metadata\PropertyMetadata;
use Neo4j\OGM\Metadata\RelationshipAnnotationMetadata;
use Neo4j\OGM\Metadata\RelationshipMetadata;

class NodeAnnotationMetadataFactory implements NodeAnnotationMetadataFactoryInterface
{
    private Reader $reader;

    private EntityAnnotationMetadataFactory $entityAnnotationMetadataFactory;

    private RelationshipAnnotationMetadataFactory $relationshipAnnotationMetadataFactory;

    private PropertyAnnotationMetadataFactory $propertyAnnotationMetadataFactory;

    private IdAnnotationMetadataFactory $IdAnnotationMetadataFactory;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->entityAnnotationMetadataFactory = new EntityAnnotationMetadataFactory($reader);
        $this->relationshipAnnotationMetadataFactory = new RelationshipAnnotationMetadataFactory($reader);
        $this->propertyAnnotationMetadataFactory = new PropertyAnnotationMetadataFactory($reader);
        $this->IdAnnotationMetadataFactory = new IdAnnotationMetadataFactory($reader);
    }

    public function create(string $className)
    {
        $reflectionClass = new \ReflectionClass($className);
        $entityIdMetadata = null;
        $propertiesMetadata = [];

        if (null !== $this->reader->getClassAnnotation($reflectionClass, Entity::class)) {
            $annotationMetadata = $this->entityAnnotationMetadataFactory->create($className);
        } elseif (null !== $this->reader->getClassAnnotation($reflectionClass, Relationship::class)) {
            $annotationMetadata = $this->relationshipAnnotationMetadataFactory->create($className);
        } else {
            $annotationMetadata = null;
        }

        if (null === $annotationMetadata) {
            if (false !== get_parent_class($className)) {
                return $this->create(get_parent_class($className));
            }

            throw new MappingException(sprintf('The class "%s" is not a valid OGM node', $className));
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (null !== $fieldAnnotationMetadata = $this->propertyAnnotationMetadataFactory->create($className, $reflectionProperty->getName())) {
                $converter = $this->reader->getPropertyAnnotation($reflectionProperty, Convert::class);
                $propertiesMetadata[] = new PropertyMetadata($reflectionProperty->getName(), $reflectionProperty, $fieldAnnotationMetadata, $converter);
            } elseif (null !== $fieldAnnotationMetadata = $this->IdAnnotationMetadataFactory->create($className, $reflectionProperty)) {
                $entityIdMetadata = new IdMetadata($reflectionProperty->getName(), $reflectionProperty, $fieldAnnotationMetadata);
            }
        }
        if (null === $entityIdMetadata) {
            throw new MappingException(sprintf('The class "%s" must have ID mapping defined', $className));
        }
        if ($annotationMetadata instanceof EntityAnnotationMetadata) {
            return new EntityMetadata($annotationMetadata, $entityIdMetadata, $className, $reflectionClass, $propertiesMetadata);
        }
        if ($annotationMetadata instanceof RelationshipAnnotationMetadata) {
            return new RelationshipMetadata($annotationMetadata, $entityIdMetadata, $className, $reflectionClass, $propertiesMetadata);
        }
    }
}
