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

class IdMetadata
{
    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var \ReflectionProperty
     */
    protected $reflectionProperty;

    /**
     * @param string                                   $propertyName
     * @param \Neo4j\OGM\Metadata\IdAnnotationMetadata $idAnnotationMetadata
     */
    public function __construct($propertyName, \ReflectionProperty $reflectionProperty)
    {
        $this->propertyName = $propertyName;
        $this->reflectionProperty = $reflectionProperty;
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    public function getValue($object)
    {
        $this->reflectionProperty->setAccessible(true);

        return $this->reflectionProperty->getValue($object);
    }

    /**
     * @param $object
     * @param $value
     */
    public function setValue($object, $value)
    {
        $this->reflectionProperty->setAccessible(true);
        $this->reflectionProperty->setValue($object, $value);
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }
}
