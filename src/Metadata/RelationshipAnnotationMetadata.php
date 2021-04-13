<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Metadata;

class RelationshipAnnotationMetadata
{
    protected $type;

    protected $repository;

    protected $unique;

    protected $startNode;

    protected $endNode;

    public function __construct(string $type, string $repository, bool $unique, RelationshipNodeMetadata $startNode, RelationshipNodeMetadata $endNode)
    {
        $this->type = $type;
        $this->repository = $repository;
        $this->unique = $unique;
        $this->startNode = $startNode;
        $this->endNode = $endNode;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getStartNode(): RelationshipNodeMetadata
    {
        return $this->startNode;
    }

    public function getEndNode(): RelationshipNodeMetadata
    {
        return $this->endNode;
    }
}
