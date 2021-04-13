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

use Neo4j\OGM\Model\NodeInterface;

abstract class AbstractNodeMetadata implements ClassMetadata
{
    protected $entityIdMetadata;

    protected $className;

    protected $repository;

    protected $reflectionClass;

    protected $nodePropertiesMetadata = [];

    public function __construct(
        IdMetadata $entityIdMetadata,
        string $className,
        string $repository,
        \ReflectionClass $reflectionClass,
        array $nodePropertiesMetadata
    ) {
        $this->entityIdMetadata = $entityIdMetadata;
        $this->className = $className;
        $this->repository = $repository;
        $this->reflectionClass = $reflectionClass;
        foreach ($nodePropertiesMetadata as $meta) {
            if ($meta instanceof PropertyMetadata) {
                $this->nodePropertiesMetadata[$meta->getPropertyName()] = $meta;
            }
        }
    }

    public function getName(): string
    {
        return $this->className;
    }

    public function getReflectionClass(): \ReflectionClass
    {
        return $this->reflectionClass;
    }

    public function hasFields(): bool
    {
        return !empty($this->nodePropertiesMetadata);
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function newInstance(): NodeInterface
    {
        return $this->reflectionClass->newInstance();
    }

    public function getIdValue($object): ?int
    {
        return $this->entityIdMetadata->getValue($object);
    }

    public function setIdValue($object, $value): void
    {
        $this->entityIdMetadata->setValue($object, $value);
    }

    /**
     * @return PropertyMetadata[]
     */
    public function getPropertiesMetadata(): array
    {
        return $this->nodePropertiesMetadata;
    }

    public function getNodeIdentifier(): string
    {
        return strtolower(str_replace('\\', '_', $this->className));
    }
}
