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
use Neo4j\OGM\Annotation\QueryResult;
use Neo4j\OGM\Exception\MappingException;
use Neo4j\OGM\Metadata\QueryResultAnnotationMetadata;

final class QueryResultAnnotationMetadataFactory
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function create(string $className, string $property): ?QueryResultAnnotationMetadata
    {
        $reflectionClass = new \ReflectionClass($className);
        if ($reflectionClass->hasProperty($property)) {
            /** @var QueryResult $annotation */
            $annotation = $this->reader->getPropertyAnnotation($reflectionClass->getProperty($property), QueryResult::class);

            if (null !== $annotation) {
                if (!$annotation->query) {
                    throw new MappingException(sprintf('"%s::%s" you need to specify a value for "query"', $className, $property));
                }
                if (!strstr($annotation->query, '{ENTRY}')) {
                    throw new MappingException(sprintf('"%s::%s" the value for "query" needs to include a "{ENTRY}"', $className, $property));
                }
                if (!strstr($annotation->query, '{OUTPUT}')) {
                    throw new MappingException(sprintf('"%s::%s" the value for "query" needs to include a "{OUTPUT}"', $className, $property));
                }

                return new QueryResultAnnotationMetadata($annotation->query, $annotation->orderBy, $annotation->limit, $annotation->collection);
            }
        }

        return null;
    }
}
