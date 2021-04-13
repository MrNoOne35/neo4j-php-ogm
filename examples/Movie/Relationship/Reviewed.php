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
 * @OGM\Relationship(type="REVIEWED")
 */
class Reviewed implements RelationshipInterface
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
     * @OGM\Property(type="string")
     */
    private string $summary;

    /**
     * @OGM\Property(type="int")
     */
    private int $rating;

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

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }
}
