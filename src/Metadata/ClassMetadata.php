<?php

namespace Neo4j\OGM\Metadata;

use Neo4j\OGM\Model\NodeInterface;

interface ClassMetadata
{
    public function getName(): string;

    public function getReflectionClass(): \ReflectionClass;

    public function hasFields(): bool;

    public function getRepository(): string;

    public function newInstance(): NodeInterface;

    public function getIdValue($object): ?int;

    public function setIdValue($object, $value): void;

    /**
     * @return PropertyMetadata[]
     */
    public function getPropertiesMetadata(): array;

    public function getNodeIdentifier(): string;
}
