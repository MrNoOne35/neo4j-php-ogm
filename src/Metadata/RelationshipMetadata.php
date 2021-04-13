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

use Neo4j\OGM\Model\EntityInterface;

class RelationshipMetadata extends AbstractNodeMetadata
{
    protected $relationshipAnnotationMetadata;

    public function __construct(
        RelationshipAnnotationMetadata $relationshipAnnotationMetadata,
        IdMetadata $entityIdMetadata,
        string $className,
        \ReflectionClass $reflectionClass,
        array $nodePropertiesMetadata
    ) {
        parent::__construct(
            $entityIdMetadata,
            $className,
            $relationshipAnnotationMetadata->getRepository(),
            $reflectionClass,
            $nodePropertiesMetadata
        );
        $this->relationshipAnnotationMetadata = $relationshipAnnotationMetadata;
    }

    public function getType(): string
    {
        return $this->relationshipAnnotationMetadata->getType();
    }

    public function isUnique(): bool
    {
        return $this->relationshipAnnotationMetadata->isUnique();
    }

    public function getStartKey(): string
    {
        return $this->relationshipAnnotationMetadata->getStartNode()->getPropertyName();
    }

    public function getStartEntity($object): ?EntityInterface
    {
        return $this->relationshipAnnotationMetadata->getStartNode()->getValue($object);
    }

    public function setStartEntity($object, ?EntityInterface $value): void
    {
        $this->relationshipAnnotationMetadata->getStartNode()->setValue($object, $value);
    }

    public function getStartClassName(): string
    {
        return $this->relationshipAnnotationMetadata->getStartNode()->getClassName();
    }

    public function getEndKey(): string
    {
        return $this->relationshipAnnotationMetadata->getEndNode()->getPropertyName();
    }

    public function getEndEntity($object): ?EntityInterface
    {
        return $this->relationshipAnnotationMetadata->getEndNode()->getValue($object);
    }

    public function setEndEntity($object, ?EntityInterface $value): void
    {
        $this->relationshipAnnotationMetadata->getEndNode()->setValue($object, $value);
    }

    public function getEndClassName(): string
    {
        return $this->relationshipAnnotationMetadata->getEndNode()->getClassName();
    }
}
