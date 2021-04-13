<?php

/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Proxy;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neo4j\OGM\Model\NodeInterface;
use Neo4j\OGM\NodeManagerInterface;

class CollectionProxy extends AbstractLazyCollection implements CollectionProxyInterface
{
    protected NodeManagerInterface $nm;
    protected NodeInterface $node;
    protected string $field;
    protected \ReflectionProperty $property;

    public function __construct(NodeManagerInterface $nm, NodeInterface $node, string $field, string $property)
    {
        $classMetadata = $nm->getMetadataCache()->getClassMetadata(get_class($node));
        $this->property = $classMetadata->getReflectionClass()->getProperty($property);
        if (!$this->property->isPublic()) {
            $this->property->setAccessible(true);
        }
        $this->nm = $nm;
        $this->node = $node;
        $this->field = $field;
    }

    protected function doInitialize()
    {
        $collection = $this->property->getValue($this->node);
        if (!($collection instanceof CollectionProxyInterface)
            && $collection instanceof Collection) {
            // The value was already switched by someone else
            $this->collection = $collection;

            return;
        }

        $stmt = $this->nm->getQueryBuilder()->getLoadRelationQuery(
            $this->node,
            'lazy_collection',
            $this->field
        );
        if (!$stmt) {
            $this->collection = new ArrayCollection();

            return;
        }

        $result = $this->nm->getClient()->runStatement($stmt);
        if (!$result || !count($result)) {
            $this->collection = new ArrayCollection();

            return;
        }

        try {
            $values = $result->first()->get('lazy_collection_value');
        } catch (\Throwable $e) {
            $this->collection = new ArrayCollection();

            return;
        }

        $this->nm->getHydrator()->popuplate($this->nm, $this->node, $values);

        // popuplate will have hydrated the node with an actual Collection
        // we need to fetch it from the node.
        $collection = $this->property->getValue($this->node);
        if (!$collection || !($collection instanceof Collection)) {
            $this->collection = new ArrayCollection();

            return;
        }
        $this->collection = $collection;

        return true;
    }
}
