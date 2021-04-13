<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Repository;

use Doctrine\Common\Collections\Criteria;
use Neo4j\OGM\Event\NodeCreatedEvent;
use Neo4j\OGM\Event\NodeDeletedEvent;
use Neo4j\OGM\Event\NodeUpdatedEvent;
use Neo4j\OGM\Metadata\ClassMetadata;
use Neo4j\OGM\Model\NodeInterface;
use Neo4j\OGM\NodeManagerInterface;

class BaseRepository implements RepositoryInterface
{
    protected string $className;

    protected ClassMetadata $classMetadata;

    protected NodeManagerInterface $nm;

    public function __construct(
        NodeManagerInterface $nm,
        string $className
    ) {
        $this->nm = $nm;
        $this->className = $className;
        $this->classMetadata = $this->nm->getMetadataCache()->getClassMetadata($className);

        $nm->setRepository($className, $this);
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function find(int $id): ?NodeInterface
    {
        $cachedNode = $this->nm->getNodesCache()->get($this->className, $id);
        if ($cachedNode) {
            return $cachedNode;
        }

        return $this->findOneBy(['id()' => $id]);
    }

    public function findAll(): ?array
    {
        return $this->findBy([]);
    }

    public function findBy(array $filters, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        $criteria = $this->buildCriteria($filters, $orderBy, $offset, $limit);

        $identifier = $this->getIdentifier();
        $stmt = $this->nm->getQueryBuilder()->getSearchQuery($this->className, $identifier, $criteria);
        $result = $this->nm->getClient()->runStatement($stmt);

        return $this->hydrateEntities($identifier, $result);
    }

    public function findOneBy(array $filters, array $orderBy = null): ?NodeInterface
    {
        $criteria = $this->buildCriteria($filters, $orderBy, null, 1);

        $identifier = $this->getIdentifier();
        $stmt = $this->nm->getQueryBuilder()->getSearchQuery($this->className, $identifier, $criteria);
        $result = $this->nm->getClient()->runStatement($stmt);
        if (count($result) > 1) {
            throw new \LogicException(sprintf('Expected only 1 record, got %d', count($result)));
        }
        if (!count($result)) {
            return null;
        }
        $entities = $this->hydrateEntities($identifier, $result);

        return !empty($entities) ? $entities[0] : null;
    }

    public function matching(Criteria $criteria): ?array
    {
        $identifier = $this->getIdentifier();
        $stmt = $this->nm->getQueryBuilder()->getSearchQuery($this->className, $identifier, $criteria);
        $result = $this->nm->getClient()->runStatement($stmt);

        return $this->hydrateEntities($identifier, $result);
    }

    public function save(NodeInterface $node): int
    {
        $identifier = $this->getIdentifier();
        $id = $this->classMetadata->getIdValue($node);
        $insert = null === $id;

        $stmt = $insert ?
            $this->nm->getQueryBuilder()->getCreateQuery($node, $identifier)
            :
            $this->nm->getQueryBuilder()->getUpdateQuery($node, $identifier);

        if (null === $stmt) {
            return 0;
        }

        $result = $this->nm->getClient()->runStatement($stmt);
        if (!count($result)) {
            return 0;
        }

        if ($insert) {
            $entry = $result->get(0);
            if (!$entry instanceof \Ds\Map) {
                throw new \RuntimeException('Failed to handle inserted node: unexpected value');
            }

            try {
                $id = $entry->get($identifier.'_id');
            } catch (\Throwable $e) {
                throw new \RuntimeException('Failed to handle inserted node: unexpected value');
            }
            $this->classMetadata->setIdValue($node, $id);
            $this->nm->getEventDispatcher()->dispatch(new NodeCreatedEvent($node));
        } else {
            $this->nm->getEventDispatcher()->dispatch(new NodeUpdatedEvent($node));
        }

        $this->nm->getNodesCache()->put($this->className, $id, $node);

        return count($result);
    }

    public function delete(NodeInterface $node): int
    {
        $id = $this->classMetadata->getIdValue($node);
        if (null === $id) {
            return 0;
        }

        $this->nm->getNodesCache()->remove($this->className, $id);

        $identifier = $this->getIdentifier();

        $stmt = $this->nm->getQueryBuilder()->getDetachDeleteQuery($node, $identifier);
        $result = $this->nm->getClient()->runStatement($stmt);

        try {
            if (count($result) && $result->first()->get('ctr')) {
                $this->nm->getEventDispatcher()->dispatch(new NodeDeletedEvent($node));

                return $result->first()->get('ctr');
            }
        } catch (\Throwable $e) {
        }

        return 0;
    }

    public function refresh(NodeInterface $node): void
    {
        $this->reload($node);
        $this->nm->getEventDispatcher()->dispatch(new NodeUpdatedEvent($node));
    }

    public function reload(NodeInterface $node): void
    {
        $identifier = $this->getIdentifier();
        $id = $this->classMetadata->getIdValue($node);
        if (null === $id) {
            return;
        }

        $criteria = $this->buildCriteria(['id()' => $id], null, null, 1);

        $stmt = $this->nm->getQueryBuilder()->getSearchQuery($this->className, $identifier, $criteria);
        $result = $this->nm->getClient()->runStatement($stmt);
        if (1 !== count($result)) {
            throw new \LogicException(sprintf('Expected only 1 record, got %d', count($result)));
        }
        $values = $result->first()->get($identifier.'_value');
        $this->nm->getHydrator()->popuplate($this->nm, $node, $values);
    }

    protected function buildCriteria(array $filters, ?array $orderings = null, $firstResult = null, $maxResults = null): Criteria
    {
        $expressionBuilder = Criteria::expr();
        $criteria = new Criteria(null, $orderings, $firstResult, $maxResults);
        foreach ($filters as $field => $value) {
            $criteria->andWhere($expressionBuilder->eq($field, $value));
        }

        return $criteria;
    }

    protected function getIdentifier(): string
    {
        return $this->classMetadata->getNodeIdentifier();
    }

    /** @return NodeInterface[] */
    protected function hydrateEntities(string $identifier, \Ds\Vector $entries): array
    {
        $entities = [];
        foreach ($entries as $entry) {
            $node = new $this->className();
            $values = $entry->get($identifier.'_value');
            $this->nm->getHydrator()->popuplate($this->nm, $node, $values);
            $entities[] = $node;
        }

        return $entities;
    }
}
