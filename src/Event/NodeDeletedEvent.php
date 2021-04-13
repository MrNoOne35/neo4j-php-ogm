<?php

namespace Neo4j\OGM\Event;

use Neo4j\OGM\Model\NodeInterface;

class NodeDeletedEvent
{
    protected $node;

    public function __construct(NodeInterface $node)
    {
        $this->node = $node;
    }

    public function getNode(): NodeInterface
    {
        return $this->node;
    }
}
