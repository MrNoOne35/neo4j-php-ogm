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
use Neo4j\OGM\Model\NodeInterface;

interface RepositoryInterface
{
    public function getClassName(): string;

    public function find(int $id): ?NodeInterface;

    /** @return NodeInterface[]|null[] */
    public function findAll(): ?array;

    /** @return NodeInterface[]|null[] */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array;

    public function findOneBy(array $criteria, array $orderBy = null): ?NodeInterface;

    /** @return NodeInterface[]|null[] */
    public function matching(Criteria $criteria): ?array;

    /**
     * Create or update a node.
     * Triggers a NodeCreatedEvent or NodeUpdatedEvent event.
     *
     * @return The number of created or updated nodes
     */
    public function save(NodeInterface $node): int;

    /**
     * Delete a node.
     * Triggers a NodeDeletedEvent event.
     *
     * @return The number of deleted nodes
     */
    public function delete(NodeInterface $node): int;

    /**
     * Reloads all data of a node. Triggers a NodeUpdatedEvent event.
     *
     * @return true if the node was reloaded
     */
    public function refresh(NodeInterface $node): void;

    /**
     * Reloads all data of a node, but don't trigger any event.
     *
     * @return true if the node was reloaded
     */
    public function reload(NodeInterface $node): void;
}
