<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Tests\Entity\BothLazy;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Common\Direction;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="TestParent")
 */
class TestParent implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\Property(type="string")
     */
    private $name;

    /**
     * @OGM\Relation(relationship=TestBelongsTo::class, direction=Direction::INCOMING,relationshipProperty="meta",collection=false)
     */
    private $child;

    private $meta;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getChild(): TestChild
    {
        return $this->child;
    }

    public function setChild(TestChild $child): self
    {
        $this->child = $child;

        return $this;
    }

    public function getMeta(): TestBelongsTo
    {
        return $this->meta;
    }

    public function setMeta(TestBelongsTo $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
