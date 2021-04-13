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

class EntityAnnotationMetadata
{
    protected string $label;

    protected string $repository;

    /** @var RelationMetadata[] */
    protected array $relations;

    /** @var QueryResultMetadata[] */
    protected array $queryResults;

    public function __construct(string $label, string $repository, array $relations, array $queryResults)
    {
        $this->label = $label;
        $this->repository = $repository;
        $this->relations = $relations;
        $this->queryResults = $queryResults;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    /**
     * @return RelationMetadata[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return QueryResultMetadata[]
     */
    public function getQueryResults(): array
    {
        return $this->queryResults;
    }
}
