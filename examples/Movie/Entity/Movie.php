<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Examples\Movie\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Common\Direction;
use Neo4j\OGM\Examples\Movie\Relationship\ActedIn;
use Neo4j\OGM\Examples\Movie\Relationship\Directed;
use Neo4j\OGM\Examples\Movie\Relationship\Produced;
use Neo4j\OGM\Examples\Movie\Relationship\Reviewed;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Movie")
 */
class Movie implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\Property(type="string")
     */
    private $title;

    /**
     * @OGM\Property(type="int")
     */
    private $released;

    /**
     * @OGM\Property(type="string")
     */
    private $tagline;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,orderBy={"born"="ASC"},relationshipProperty="actorsMeta")
     */
    private $actors;
    private $actorsMeta;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,orderBy={"born"="ASC"},relationshipProperty="femaleActorsMeta",filters={"gender"="FEMALE"})
     */
    private $femaleActors;
    private $femaleActorsMeta;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,orderBy={"born"="ASC"},relationshipProperty="femaleActorsMeta",filters={"gender"="MALE"})
     */
    private $maleActors;
    private $maleActorsMeta;

    /**
     * @OGM\Relation(relationship=Directed::class,direction=Direction::INCOMING,collection=true)
     */
    private $directors;

    /**
     * @OGM\Relation(relationship=Produced::class,direction=Direction::INCOMING,collection=true)
     */
    private $producers;

    /**
     * @OGM\Relation(relationship=Reviewed::class,direction=Direction::INCOMING,collection=true,relationshipProperty="reviewsMeta")
     */
    private $reviews;
    private $reviewsMeta;

    /**
     * @OGM\QueryResult(query="MATCH ({ENTRY})<-[r:REVIEWED]-(:Person) RETURN avg(r.rating) AS {OUTPUT}")
     */
    private $rating;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,limit=2,orderBy={"born"="ASC"})
     */
    private $oldestTwoActors;

    /**
     * @OGM\QueryResult(query="MATCH ({ENTRY})<-[:ACTED_IN]-(actor:Person) RETURN avg({ENTRY}.released - actor.born) AS {OUTPUT}")
     */
    private $averageActorsAge;

    public function __construct()
    {
        $this->actors = new ArrayCollection();
        $this->actorsMeta = new ArrayCollection();
        $this->directors = new ArrayCollection();
        $this->producers = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->reviewsMeta = new ArrayCollection();
        $this->oldestTwoActors = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getReleased(): int
    {
        return $this->released;
    }

    public function setReleased(int $released): self
    {
        $this->released = $released;

        return $this;
    }

    public function getTagline(): string
    {
        return $this->tagline;
    }

    public function setTagline(string $tagline): self
    {
        $this->tagline = $tagline;

        return $this;
    }

    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function getActorsMeta(): Collection
    {
        return $this->actorsMeta;
    }

    public function getFemaleActors(): Collection
    {
        return $this->femaleActors;
    }

    public function getFemaleActorsMeta(): Collection
    {
        return $this->femaleActorsMeta;
    }

    public function getMaleActors(): Collection
    {
        return $this->maleActors;
    }

    public function getMaleActorsMeta(): Collection
    {
        return $this->maleActorsMeta;
    }

    public function getDirectors(): Collection
    {
        return $this->directors;
    }

    public function getProducers(): Collection
    {
        return $this->producers;
    }

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function getReviewsMeta(): Collection
    {
        return $this->reviewsMeta;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function getOldestTwoActors(): Collection
    {
        return $this->oldestTwoActors;
    }

    public function getAverageActorsAge(): float
    {
        return $this->averageActorsAge;
    }
}
