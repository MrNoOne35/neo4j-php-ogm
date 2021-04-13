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

class QueryResultMetadata
{
    protected $propertyName;

    protected $reflectionProperty;

    protected $queryResultAnnotationMetadata;

    protected $isAccessible;

    public function __construct(
        string $propertyName,
        \ReflectionProperty $reflectionProperty,
        QueryResultAnnotationMetadata $queryResultAnnotationMetadata
    ) {
        $this->propertyName = $propertyName;
        $this->reflectionProperty = $reflectionProperty;
        $this->queryResultAnnotationMetadata = $queryResultAnnotationMetadata;
        $this->isAccessible = $reflectionProperty->isPublic();
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getReflectionProperty(): \ReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function getQuery(): string
    {
        return $this->queryResultAnnotationMetadata->getQuery();
    }

    public function getOrderBy(): ?array
    {
        return $this->queryResultAnnotationMetadata->getOrderBy();
    }

    public function getLimit(): ?int
    {
        return $this->queryResultAnnotationMetadata->getLimit();
    }

    public function isCollection(): bool
    {
        return $this->queryResultAnnotationMetadata->isCollection();
    }

    public function setValue($object, $value): void
    {
        $this->checkAccess();
        $this->reflectionProperty->setValue($object, $value);
    }

    public function getValue($object)
    {
        $this->checkAccess();

        return $this->reflectionProperty->getValue($object);
    }

    protected function checkAccess(): void
    {
        if (!$this->isAccessible) {
            $this->reflectionProperty->setAccessible(true);
        }
        $this->isAccessible = true;
    }
}
