<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\QueryBuilder;

use Doctrine\Common\Collections\Criteria;
use Laudis\Neo4j\Databags\Statement;
use Neo4j\OGM\Model\NodeInterface;

interface QueryBuilderInterface
{
    /**
     * This method builds a complete search query, from the initial MATCH, to the final RETURN.
     *
     * @param string   $className  the entity/relationship to search
     * @param string   $identifier the identifier of the object to return
     * @param Criteria $criteria   the criteria to apply to the search
     *
     * @return Statement the statement to be executed
     */
    public function getSearchQuery(string $className, string $identifier, Criteria $criteria): Statement;

    /**
     * This method only builds the final part of a query, which allows an entity/relationship to be hydrated.
     *
     * @param string     $className  the entity/relationship to search
     * @param string     $identifier the identifier of the object to return
     * @param array      $params     will be populated by eventual query params
     * @param null|array $orderBy    an array of properties to sort by
     * @param null|mixed $limit      the number of nodes to return
     * @param null|mixed $offset     the offset of nodes to return
     *
     * @return string the final part of the query
     */
    public function getCustomSearchQuery(string $className, string $identifier, array &$params, ?array $orderBy, ?int $limit, ?int $offset): string;

    public function getCountQuery(string $className, string $identifier, Criteria $criteria): Statement;

    public function getCreateQuery(NodeInterface $object, string $identifier): Statement;

    /**
     * @return null|Statement null is returned if the node has no properties to set
     */
    public function getUpdateQuery(NodeInterface $object, string $identifier): ?Statement;

    public function getDetachDeleteQuery(NodeInterface $object, string $identifier): Statement;

    public function getDeleteQuery(NodeInterface $object, string $identifier): Statement;

    /**
     * @return null|Statement null is returned if the property doesn't exist or is not a relation
     */
    public function getLoadRelationQuery(NodeInterface $object, string $identifier, string $property): ?Statement;
}
