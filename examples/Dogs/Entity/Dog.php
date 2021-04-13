<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Examples\Dogs\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Common\Direction;
use Neo4j\OGM\Examples\Dogs\Gender;
use Neo4j\OGM\Examples\Dogs\Relationship\Breeded;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Dog")
 */
class Dog implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\Property(type="string", nullable=false)
     */
    private $name;

    /**
     * @OGM\Property(type="string", nullable=false)
     */
    private $gender;

    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime")
     */
    private $birthdate;

    /**
     * @OGM\Relation(relationship=Breeded::class, direction=Direction::OUTGOING, collection=true, orderBy={"birthdate"="ASC"},relationshipProperty="cubsMeta")
     */
    private $cubs;
    private $cubsMeta;

    /**
     * @OGM\Relation(relationship=Breeded::class, direction=Direction::INCOMING, filters={"gender"=Gender::FEMALE},relationshipProperty="motherMeta")
     */
    private $mother;
    private $motherMeta;

    /**
     * @OGM\Relation(relationship=Breeded::class, direction=Direction::INCOMING, filters={"gender"=Gender::MALE},relationshipProperty="fatherMeta")
     */
    private $father;
    private $fatherMeta;

    public function __construct()
    {
        $this->cubs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /** @return Dog[] */
    public function getCubs(): Collection
    {
        return $this->cubs;
    }

    public function getCubsMeta(): Collection
    {
        return $this->cubsMeta;
    }

    public function getMother(): ?Dog
    {
        return $this->mother;
    }

    public function getMotherMeta(): ?Breeded
    {
        return $this->motherMeta;
    }

    public function getFather(): ?Dog
    {
        return $this->father;
    }

    public function getFatherMeta(): ?Breeded
    {
        return $this->fatherMeta;
    }
}
