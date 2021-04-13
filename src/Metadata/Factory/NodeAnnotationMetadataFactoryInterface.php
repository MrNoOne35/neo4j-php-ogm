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

use Neo4j\OGM\Metadata\EntityMetadata;
use Neo4j\OGM\Metadata\RelationshipMetadata;

interface NodeAnnotationMetadataFactoryInterface
{
    /** @return EntityMetadata|RelationshipMetadata */
    public function create(string $className);
}
