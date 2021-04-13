<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Tests\Entity\UniqueRelationship;

use Neo4j\OGM\Annotation as OGM;
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
     * @OGM\QueryResult(query="MATCH ({ENTRY})<-[r:TestRelationshipUnique]-(:TestChild) RETURN count(r) AS {OUTPUT}")
     */
    private $uniqueCount;

    /**
     * @OGM\QueryResult(query="MATCH ({ENTRY})<-[r:TestRelationshipNotUnique]-(:TestChild) RETURN count(r) AS {OUTPUT}")
     */
    private $notUniqueCount;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUniqueCount(): ?int
    {
        return $this->uniqueCount;
    }

    public function getNotUniqueCount(): ?int
    {
        return $this->notUniqueCount;
    }
}
