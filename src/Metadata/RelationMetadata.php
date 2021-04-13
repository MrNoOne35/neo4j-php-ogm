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

class RelationMetadata extends AbstractPropertyMetadata
{
    protected $reflectionRelationshipProperty;

    protected $relationAnnotationMetadata;

    protected $isTargetAccessible;

    public function __construct(
        string $propertyName,
        \ReflectionProperty $reflectionProperty,
        ?\ReflectionProperty $reflectionRelationshipProperty,
        RelationAnnotationMetadata $relationAnnotationMetadata
    ) {
        parent::__construct($propertyName, $reflectionProperty);
        $this->reflectionRelationshipProperty = $reflectionRelationshipProperty;
        $this->relationAnnotationMetadata = $relationAnnotationMetadata;
        $this->isTargetAccessible = $reflectionRelationshipProperty ? $reflectionRelationshipProperty->isPublic() : false;
    }

    public function getRelationship(): string
    {
        return $this->relationAnnotationMetadata->getRelationship();
    }

    public function getDirection(): string
    {
        return $this->relationAnnotationMetadata->getDirection();
    }

    public function getOrderBy(): ?array
    {
        return $this->relationAnnotationMetadata->getOrderBy();
    }

    public function getLimit(): ?int
    {
        return $this->relationAnnotationMetadata->getLimit();
    }

    public function getRelationshipPropertyName(): ?string
    {
        return $this->relationAnnotationMetadata->getRelationshipPropertyName();
    }

    public function getFetch(): string
    {
        return $this->relationAnnotationMetadata->getFetch();
    }

    public function isCollection(): bool
    {
        return $this->relationAnnotationMetadata->isCollection();
    }

    public function getFilters(): ?array
    {
        return $this->relationAnnotationMetadata->getFilters();
    }

    public function setRelationshipValue($object, $value): void
    {
        if (!$this->reflectionRelationshipProperty) {
            return;
        }
        $this->checkRelationshipAccess();
        $this->reflectionRelationshipProperty->setValue($object, $value);
    }

    public function getRelationshipValue($object)
    {
        if (!$this->reflectionRelationshipProperty) {
            return null;
        }

        $this->checkRelationshipAccess();

        return $this->reflectionRelationshipProperty->getValue($object);
    }

    protected function checkRelationshipAccess(): void
    {
        if (!$this->reflectionRelationshipProperty) {
            return;
        }
        if (!$this->isTargetAccessible) {
            $this->reflectionRelationshipProperty->setAccessible(true);
        }
        $this->isTargetAccessible = true;
    }
}
