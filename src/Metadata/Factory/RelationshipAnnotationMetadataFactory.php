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
use Neo4j\OGM\Annotation\EndEntity;
use Neo4j\OGM\Annotation\Relationship;
use Neo4j\OGM\Annotation\StartEntity;
use Neo4j\OGM\Exception\MappingException;
use Neo4j\OGM\Metadata\RelationshipAnnotationMetadata;
use Neo4j\OGM\Metadata\RelationshipNodeMetadata;
use Neo4j\OGM\Util\ClassUtils;

final class RelationshipAnnotationMetadataFactory
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function create(string $className): RelationshipAnnotationMetadata
    {
        $reflectionClass = new \ReflectionClass($className);
        /** @var Relationship $annotation */
        $annotation = $this->reader->getClassAnnotation($reflectionClass, Relationship::class);
        if (!$annotation) {
            throw new MappingException(sprintf('The class "%s" is missing the "%s" annotation', $className, Relationship::class));
        }
        if (!$annotation->type) {
            throw new MappingException(sprintf('"%s::%s" you need to specify a value for "type"', $className, Relationship::class));
        }

        $startNode = null;
        $endNode = null;

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (null === $startNode && null !== $startAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, StartEntity::class)) {
                $startNodeClassName = ClassUtils::getFullClassName($startAnnotation->target, $className);
                $startNode = new RelationshipNodeMetadata($reflectionProperty->getName(), $startNodeClassName, $reflectionProperty);
            } elseif (null === $endNode && null !== $endAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, EndEntity::class)) {
                $endNodeClassName = ClassUtils::getFullClassName($endAnnotation->target, $className);
                $endNode = new RelationshipNodeMetadata($reflectionProperty->getName(), $endNodeClassName, $reflectionProperty);
            }
        }
        if (!$startNode) {
            throw new MappingException(sprintf('The class "%s" is missing the "%s" annotation', $className, StartEntity::class));
        }
        if (!$endNode) {
            throw new MappingException(sprintf('The class "%s" is missing the "%s" annotation', $className, EndEntity::class));
        }

        return new RelationshipAnnotationMetadata($annotation->type, $annotation->repository, $annotation->unique, $startNode, $endNode);
    }
}
