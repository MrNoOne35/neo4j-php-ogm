<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM;

use Laudis\Neo4j\Contracts\ClientInterface;
use Neo4j\OGM\Hydrator\HydratorInterface;
use Neo4j\OGM\Metadata\Cache\MetadataCacheInterface;
use Neo4j\OGM\NodesCache\NodesCacheInterface;
use Neo4j\OGM\QueryBuilder\QueryBuilderInterface;
use Neo4j\OGM\Repository\RepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

interface NodeManagerInterface
{
    /**
     * Clear any internal cache.
     */
    public function clear(): void;

    public function getClient(): ClientInterface;

    public function getMetadataCache(): MetadataCacheInterface;

    public function getHydrator(): HydratorInterface;

    public function getQueryBuilder(): QueryBuilderInterface;

    public function getRepository(string $className): RepositoryInterface;

    public function setRepository(string $className, RepositoryInterface $repository): void;

    public function getEventDispatcher(): EventDispatcherInterface;

    public function getNodesCache(): NodesCacheInterface;
}
