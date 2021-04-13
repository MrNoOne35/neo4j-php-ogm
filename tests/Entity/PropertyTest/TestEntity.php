<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Tests\Entity\PropertyTest;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="TestEntity")
 */
class TestEntity implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\Property(type="string",nullable=true)
     */
    private $nameNullable;

    /**
     * @OGM\Property(type="string",nullable=false)
     */
    private $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNameNullable()
    {
        return $this->nameNullable;
    }

    public function setNameNullable($nameNullable): self
    {
        $this->nameNullable = $nameNullable;

        return $this;
    }
}
