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
    /**
     * @return string The node class name handled by this repository
     */
    public function getClassName(): string;

    /**
     * Finds an node by its Neo4j identifier.
     *
     * @param int $id the node id
     *
     * @return null|NodeInterface the node or null when not found
     */
    public function find(int $id): ?NodeInterface;

    /**
     * Finds all nodes in the repository.
     *
     * @return null|NodeInterface[]
     */
    public function findAll(): ?array;

    /**
     * Finds nodes by a set of criteria.
     *
     * @param array      $criteria the search criteria, the keys are properties of the handled node
     * @param null|array $orderBy  an array of properties to sort by
     * @param null|mixed $limit    the number of nodes to return
     * @param null|mixed $offset   the offset of nodes to return
     *
     * @return null|NodeInterface[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array;

    /**
     * Finds a single node by a set of criteria.
     *
     * @param array      $criteria the search criteria, the keys are properties of the handled node
     * @param null|array $orderBy  an array of properties to sort by
     */
    public function findOneBy(array $criteria, array $orderBy = null): ?NodeInterface;

    /**
     * Search nodes.
     *
     * @return null|NodeInterface[]
     */
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

    /**
     * Counts nodes by a set of criteria.
     *
     * @return int the cardinality of the nodes that match the given criteria
     */
    public function count(array $criteria): int;
}
