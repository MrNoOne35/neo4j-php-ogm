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
 * @OGM\Entity(label="TestChild")
 */
class TestChild implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    public function getId(): int
    {
        return $this->id;
    }
}
