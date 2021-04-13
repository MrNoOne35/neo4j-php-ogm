<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Tests\Entity\QueryResult;

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

    /**
     * @OGM\Property(type="string")
     */
    private $name;

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
}
