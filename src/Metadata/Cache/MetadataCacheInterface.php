<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Metadata\Cache;

use Neo4j\OGM\Metadata\ClassMetadata;
use Neo4j\OGM\Metadata\EntityMetadata;
use Neo4j\OGM\Metadata\RelationshipMetadata;

interface MetadataCacheInterface
{
    public function getClassMetadata(string $className): ClassMetadata;

    public function getRelationshipClassMetadata(string $className): RelationshipMetadata;

    public function getEntityClassMetadata(string $className): EntityMetadata;
}
