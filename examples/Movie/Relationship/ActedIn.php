<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Examples\Movie\Relationship;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Examples\Movie\Entity\Movie;
use Neo4j\OGM\Examples\Movie\Entity\Person;
use Neo4j\OGM\Model\RelationshipInterface;

/**
 * @OGM\Relationship(type="ACTED_IN")
 */
class ActedIn implements RelationshipInterface
{
    /**
     * @OGM\Id()
     */
    private ?int  $id = null;

    /**
     * @OGM\StartEntity(target=Person::class)
     */
    private ?Person $person = null;

    /**
     * @OGM\EndEntity(target=Movie::class)
     */
    private ?Movie $movie = null;

    /**
     * @OGM\Property(type="array")
     */
    private ?array $roles = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): self
    {
        $this->movie = $movie;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
