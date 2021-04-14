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
    public function getSearchQuery(string $className, string $identifier, Criteria $criteria): Statement;

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
