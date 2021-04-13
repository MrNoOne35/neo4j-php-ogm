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

class NodesCache implements NodesCacheInterface
{
    /** @var NodeInterface[] */
    protected array $entries = [];

    public function exists(string $className, int $id): bool
    {
        $key = $this->getKey($className, $id);

        return array_key_exists($key, $this->entries);
    }

    public function get(string $className, int $id): ?NodeInterface
    {
        $key = $this->getKey($className, $id);

        return array_key_exists($key, $this->entries) ? $this->entries[$key] : null;
    }

    public function put(string $className, int $id, NodeInterface $node): void
    {
        $key = $this->getKey($className, $id);
        $this->entries[$key] = $node;
    }

    public function remove(string $className, int $id): void
    {
        $key = $this->getKey($className, $id);

        if (array_key_exists($key, $this->entries)) {
            unset($this->entries[$key]);
        }
    }

    public function clear(): void
    {
        $this->entries = [];
    }

    protected function getKey(string $className, int $id): string
    {
        return $className.'-'.$id;
    }
}
