<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Tests\Entity\ParentLazy;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\RelationshipInterface;

/**
 * @OGM\Relationship(type="TestBelongsTo")
 */
class TestBelongsTo implements RelationshipInterface
{
    /**
     * @OGM\Id()
     */
    private ?int  $id = null;

    /**
     * @OGM\StartEntity(target=TestChild::class)
     */
    private ?TestChild $from = null;

    /**
     * @OGM\EndEntity(target=TestParent::class)
     */
    private ?TestParent $to = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFrom(): ?TestChild
    {
        return $this->from;
    }

    public function setFrom(?TestChild $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): ?TestParent
    {
        return $this->to;
    }

    public function setTo(?TestParent $to): self
    {
        $this->to = $to;

        return $this;
    }
}
