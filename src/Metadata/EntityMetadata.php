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

class EntityMetadata extends AbstractNodeMetadata
{
    protected EntityAnnotationMetadata $nodeAnnotationMetadata;

    /** @var RelationMetadata[] */
    protected array $nodeRelationsMetadata = [];

    /** @var QueryResultMetadata[] */
    protected array $nodeQueryResultsMetadata = [];

    public function __construct(
        EntityAnnotationMetadata $nodeAnnotationMetadata,
        IdMetadata $entityIdMetadata,
        string $className,
        \ReflectionClass $reflectionClass,
        array $nodePropertiesMetadata
    ) {
        parent::__construct(
            $entityIdMetadata,
            $className,
            $nodeAnnotationMetadata->getRepository(),
            $reflectionClass,
            $nodePropertiesMetadata
        );
        $this->nodeAnnotationMetadata = $nodeAnnotationMetadata;

        foreach ($nodeAnnotationMetadata->getRelations() as $relationMetada) {
            $this->nodeRelationsMetadata[$relationMetada->getPropertyName()] = $relationMetada;
        }
        foreach ($nodeAnnotationMetadata->getQueryResults() as $queryResultMetada) {
            $this->nodeQueryResultsMetadata[$queryResultMetada->getPropertyName()] = $queryResultMetada;
        }
    }

    public function getLabel(): string
    {
        return $this->nodeAnnotationMetadata->getLabel();
    }

    /**
     * @return RelationMetadata[]
     */
    public function getRelationsMetadata(): array
    {
        return $this->nodeRelationsMetadata;
    }

    /**
     * @return QueryResultMetadata[]
     */
    public function getQueryResultsMetadata(): array
    {
        return $this->nodeQueryResultsMetadata;
    }
}
