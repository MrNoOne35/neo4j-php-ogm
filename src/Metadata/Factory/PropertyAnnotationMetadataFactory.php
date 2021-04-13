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
use Neo4j\OGM\Annotation\Property;
use Neo4j\OGM\Metadata\PropertyAnnotationMetadata;

final class PropertyAnnotationMetadataFactory
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function create($entityClass, $property)
    {
        $reflectionClass = new \ReflectionClass($entityClass);
        if ($reflectionClass->hasProperty($property)) {
            /** @var Property $annotation */
            $annotation = $this->reader->getPropertyAnnotation($reflectionClass->getProperty($property), Property::class);

            if (null !== $annotation) {
                return new PropertyAnnotationMetadata($annotation->type, $annotation->key, $annotation->nullable);
            }
        }
    }
}
