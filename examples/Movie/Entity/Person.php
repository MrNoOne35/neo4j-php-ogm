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
use Neo4j\OGM\Examples\Movie\Relationship\Follows;
use Neo4j\OGM\Examples\Movie\Relationship\Produced;
use Neo4j\OGM\Examples\Movie\Relationship\Reviewed;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Person")
 */
class Person implements EntityInterface
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
     * @OGM\Property(type="int")
     */
    private $born;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::OUTGOING,collection=true,relationshipProperty="lastestActedInMeta",limit=4,orderBy={"released"="DESC"})
     */
    private $lastestActedIn;
    private $lastestActedInMeta;

    /**
     * @OGM\QueryResult(query="MATCH ({ENTRY})-[r:ACTED_IN]->(:Movie) RETURN count(r) AS {OUTPUT}")
     */
    private $totalActedIn;

    /**
     * @OGM\Relation(relationship=Directed::class,direction=Direction::OUTGOING,collection=true,fetch="LAZY")
     */
    private $directed;

    /**
     * @OGM\Relation(relationship=Produced::class,direction=Direction::OUTGOING,collection=true)
     */
    private $produced;

    /**
     * @OGM\Relation(relationship=Follows::class,direction=Direction::OUTGOING,collection=true)
     */
    private $follows;

    /**
     * @OGM\Relation(relationship=Follows::class,direction=Direction::INCOMING,collection=true)
     */
    private $followedBy;

    /**
     * @OGM\Relation(relationship=Reviewed::class,direction=Direction::OUTGOING,collection=true,relationshipProperty="reviewedMeta")
     */
    private $reviewed;
    private $reviewedMeta;

    public function __construct()
    {
        $this->actedIn = new ArrayCollection();
        $this->actedInMeta = new ArrayCollection();
        $this->directed = new ArrayCollection();
        $this->produced = new ArrayCollection();
        $this->follows = new ArrayCollection();
        $this->followedBy = new ArrayCollection();
        $this->reviewed = new ArrayCollection();
        $this->reviewedMeta = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getBorn(): int
    {
        return $this->born;
    }

    public function setBorn(int $born): self
    {
        $this->born = $born;

        return $this;
    }

    public function getLastestActedIn(): Collection
    {
        return $this->lastestActedIn;
    }

    public function getLastestActedInMeta(): Collection
    {
        return $this->lastestActedInMeta;
    }

    public function getDirected(): Collection
    {
        return $this->directed;
    }

    public function getProduced(): Collection
    {
        return $this->produced;
    }

    public function getFollows(): Collection
    {
        return $this->follows;
    }

    public function getFollowedBy(): Collection
    {
        return $this->followedBy;
    }

    public function getReviewed(): Collection
    {
        return $this->reviewed;
    }

    public function getReviewedMeta(): Collection
    {
        return $this->reviewedMeta;
    }

    public function getTotalActedIn(): int
    {
        return $this->totalActedIn;
    }
}
