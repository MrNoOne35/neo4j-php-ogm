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

class PropertyAnnotationMetadata
{
    protected ?string $type;

    protected ?string $key;

    protected bool $nullable;

    public function __construct(?string $type, ?string $key, bool $nullable)
    {
        $this->type = $type;
        $this->key = $key;
        $this->nullable = $nullable;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function hasCustomKey(): bool
    {
        return null !== $this->key;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
