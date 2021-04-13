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

class RelationshipNodeMetadata extends AbstractPropertyMetadata
{
    protected $className;

    public function __construct(
        string $propertyName,
        string $className,
        \ReflectionProperty $reflectionProperty
    ) {
        parent::__construct($propertyName, $reflectionProperty);
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
