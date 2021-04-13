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
use Neo4j\OGM\Annotation\Entity;
use Neo4j\OGM\Exception\MappingException;
use Neo4j\OGM\Metadata\EntityAnnotationMetadata;
use Neo4j\OGM\Metadata\QueryResultMetadata;
use Neo4j\OGM\Metadata\RelationMetadata;

final class EntityAnnotationMetadataFactory
{
    private Reader $reader;

    private RelationAnnotationMetadataFactory $relationAnnotationMetadataFactory;

    private QueryResultAnnotationMetadataFactory $queryResultAnnotationMetadataFactory;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->relationAnnotationMetadataFactory = new RelationAnnotationMetadataFactory($reader);
        $this->queryResultAnnotationMetadataFactory = new QueryResultAnnotationMetadataFactory($reader);
    }

    public function create(string $className): EntityAnnotationMetadata
    {
        $reflectionClass = new \ReflectionClass($className);
        /** @var Entity $annotation */
        $annotation = $this->reader->getClassAnnotation($reflectionClass, Entity::class);
        if (!$annotation) {
            throw new MappingException(sprintf('The class "%s" is missing the "%s" annotation', $className, Entity::class));
        }
        if (!$annotation->label) {
            throw new MappingException(sprintf('"%s::%s" you need to specify a value for "label"', $className, Entity::class));
        }

        /** @var RelationMetadata[] */
        $relationsMetadata = [];
        /** @var QueryResultMetadata[] */
        $queryResultsMetadata = [];
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (null !== $fieldAnnotationMetadata = $this->relationAnnotationMetadataFactory->create($className, $reflectionProperty->getName())) {
                $reflectionRelationshipProperty = null;
                if ($fieldAnnotationMetadata->getRelationshipPropertyName()) {
                    $reflectionRelationshipProperty = $reflectionClass->getProperty($fieldAnnotationMetadata->getRelationshipPropertyName());
                }

                $relationsMetadata[] = new RelationMetadata($reflectionProperty->getName(), $reflectionProperty, $reflectionRelationshipProperty, $fieldAnnotationMetadata);
            } elseif (null !== $queryResultAnnotationMetadata = $this->queryResultAnnotationMetadataFactory->create($className, $reflectionProperty->getName())) {
                $queryResultsMetadata[] = new QueryResultMetadata($reflectionProperty->getName(), $reflectionProperty, $queryResultAnnotationMetadata);
            }
        }

        return new EntityAnnotationMetadata($annotation->label, $annotation->repository, $relationsMetadata, $queryResultsMetadata);
    }
}
