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
use Neo4j\OGM\Repository\BaseRepository;
use Neo4j\OGM\Repository\RepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class NodeManager implements NodeManagerInterface
{
    protected ClientInterface $client;
    protected MetadataCacheInterface $metadataCache;
    protected QueryBuilderInterface $queryBuilder;
    protected HydratorInterface $hydrator;
    protected EventDispatcherInterface $eventDispatcher;
    protected NodesCacheInterface $nodesCache;

    /** @var RepositoryInterface[] */
    protected array $repositoriesCache = [];

    public function __construct(
        ClientInterface $client,
        MetadataCacheInterface $metadataCache,
        QueryBuilderInterface $queryBuilder,
        HydratorInterface $hydrator,
        NodesCacheInterface $nodesCache,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->client = $client;
        $this->metadataCache = $metadataCache;
        $this->queryBuilder = $queryBuilder;
        $this->hydrator = $hydrator;
        $this->nodesCache = $nodesCache;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function clear(): void
    {
        $this->nodesCache->clear();
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getMetadataCache(): MetadataCacheInterface
    {
        return $this->metadataCache;
    }

    public function getQueryBuilder(): QueryBuilderInterface
    {
        return $this->queryBuilder;
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator;
    }

    public function setRepository(string $className, RepositoryInterface $repository): void
    {
        $this->repositoriesCache[$className] = $repository;
    }

    public function getRepository(string $className): RepositoryInterface
    {
        $classMetadata = $this->metadataCache->getClassMetadata($className);

        if (array_key_exists($classMetadata->getName(), $this->repositoriesCache)) {
            return $this->repositoriesCache[$classMetadata->getName()];
        }

        if ($classMetadata->getRepository()) {
            $repositoryClassName = $classMetadata->getRepository();
            $repository = new $repositoryClassName($this, $classMetadata->getName());
        } else {
            $repository = new BaseRepository($this, $classMetadata->getName());
        }

        return $repository;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    public function getNodesCache(): NodesCacheInterface
    {
        return $this->nodesCache;
    }
}
