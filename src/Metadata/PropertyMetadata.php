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

use Neo4j\OGM\Annotation\Convert;

class PropertyMetadata extends AbstractPropertyMetadata
{
    protected $propertyAnnotationMetadata;

    protected $converter;

    public function __construct(
        string $propertyName,
        \ReflectionProperty $reflectionProperty,
        PropertyAnnotationMetadata $propertyAnnotationMetadata,
        Convert $converter = null
    ) {
        parent::__construct($propertyName, $reflectionProperty);
        $this->propertyAnnotationMetadata = $propertyAnnotationMetadata;
        $this->converter = $converter;
    }

    public function getType(): ?string
    {
        return $this->propertyAnnotationMetadata->getType();
    }

    public function hasCustomKey(): bool
    {
        return $this->propertyAnnotationMetadata->hasCustomKey();
    }

    public function getKey(): ?string
    {
        return $this->propertyAnnotationMetadata->getKey();
    }

    public function isNullable(): bool
    {
        return $this->propertyAnnotationMetadata->isNullable();
    }

    public function hasConverter(): bool
    {
        return null !== $this->converter;
    }

    public function getConverterType(): ?string
    {
        return $this->converter->type;
    }

    public function getConverterOptions(): ?array
    {
        return $this->converter->options;
    }
}
