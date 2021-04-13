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

class RelationAnnotationMetadata extends AbstractQueryAnnotationMetadata
{
    protected string $relationship;

    protected string $direction;

    protected bool $collection;

    protected ?string $relationshipProperty;

    protected string $fetch;

    protected ?array $filters;

    public function __construct(
        string $relationship,
        string $direction,
        ?array $orderBy,
        ?int $limit,
        ?bool $collection,
        ?string $relationshipProperty,
        string $fetch,
        ?array $filters
    ) {
        parent::__construct($orderBy, !$collection ? 1 : $limit);
        $this->relationship = $relationship;
        $this->direction = $direction;
        $this->collection = $collection ? true : false;
        $this->relationshipProperty = $relationshipProperty;
        $this->fetch = $fetch;
        $this->filters = $filters;
    }

    public function getRelationship(): string
    {
        return $this->relationship;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function isCollection(): bool
    {
        return $this->collection;
    }

    public function getRelationshipPropertyName(): ?string
    {
        return $this->relationshipProperty;
    }

    public function getFetch(): string
    {
        return $this->fetch;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }
}
