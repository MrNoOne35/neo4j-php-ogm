<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Examples\Dogs\Relationship;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Examples\Dogs\Entity\Dog;
use Neo4j\OGM\Model\RelationshipInterface;

/**
 * @OGM\Relationship(type="BREEDED", unique=true)
 */
class Breeded implements RelationshipInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\StartEntity(target=Dog::class)
     */
    private $parent;

    /**
     * @OGM\EndEntity(target=Dog::class)
     */
    private $cub;

    /**
     * @OGM\Property(type="array", nullable=false)
     */
    private $issues;

    public function __construct()
    {
        $this->issues = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParent(): ?Dog
    {
        return $this->parent;
    }

    public function setParent(Dog $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getCub(): ?Dog
    {
        return $this->cub;
    }

    public function setCub(Dog $cub): self
    {
        $this->cub = $cub;

        return $this;
    }

    public function getIssues(): array
    {
        return $this->issues;
    }

    public function setIssues(array $issues): self
    {
        $this->issues = $issues;

        return $this;
    }
}
