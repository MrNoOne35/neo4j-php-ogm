<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\NodesCache;

use Neo4j\OGM\Model\NodeInterface;

interface NodesCacheInterface
{
    public function exists(string $className, int $id): bool;

    public function get(string $className, int $id): ?NodeInterface;

    public function put(string $className, int $id, NodeInterface $node): void;

    public function remove(string $className, int $id): void;

    public function clear(): void;
}
