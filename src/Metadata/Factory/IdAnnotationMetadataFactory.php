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
use Neo4j\OGM\Annotation\Id;
use Neo4j\OGM\Metadata\IdAnnotationMetadata;

class IdAnnotationMetadataFactory
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function create(string $className, \ReflectionProperty $reflectionProperty): ?IdAnnotationMetadata
    {
        $annotations = $this->reader->getPropertyAnnotations($reflectionProperty);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Id) {
                return new IdAnnotationMetadata();
            }
        }

        return null;
    }
}
