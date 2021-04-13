<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Model;

trait NodeCreatedAtTrait
{
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime", options={"format"="long_timestamp"})
     */
    private $createdAt;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): NodeInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
