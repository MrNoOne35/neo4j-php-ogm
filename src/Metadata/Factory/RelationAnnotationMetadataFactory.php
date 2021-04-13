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
use Neo4j\OGM\Annotation\Relation;
use Neo4j\OGM\Exception\MappingException;
use Neo4j\OGM\Metadata\FetchType;
use Neo4j\OGM\Metadata\RelationAnnotationMetadata;

final class RelationAnnotationMetadataFactory
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function create(string $className, string $property): ?RelationAnnotationMetadata
    {
        $reflectionClass = new \ReflectionClass($className);
        if ($reflectionClass->hasProperty($property)) {
            /** @var Relation $annotation */
            $annotation = $this->reader->getPropertyAnnotation($reflectionClass->getProperty($property), Relation::class);

            if (null !== $annotation) {
                if ($annotation->relationshipProperty) {
                    if (!$reflectionClass->hasProperty($annotation->relationshipProperty)) {
                        throw new MappingException(sprintf('The value of "relationshipProperty" for annotation "%s" in class "%s" points to an unexisting property', Relation::class, $className));
                    }
                }

                if (!$annotation->fetch) {
                    if ($annotation->collection) {
                        $annotation->fetch = FetchType::EXTRA_LAZY;
                    } else {
                        $annotation->fetch = FetchType::LAZY;
                    }
                }

                if (!$annotation->collection && FetchType::EXTRA_LAZY === $annotation->fetch) {
                    throw new MappingException(sprintf('The value of "fetch" for annotation "%s" in class "%s" cannot be "%s" when "collection" is false', Relation::class, $className, FetchType::EXTRA_LAZY));
                }

                return new RelationAnnotationMetadata(
                    $annotation->relationship,
                    $annotation->direction,
                    $annotation->orderBy,
                    $annotation->limit,
                    $annotation->collection,
                    $annotation->relationshipProperty,
                    $annotation->fetch,
                    $annotation->filters
                );
            }
        }

        return null;
    }
}
