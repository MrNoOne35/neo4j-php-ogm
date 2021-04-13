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

trait NodeUpdatedAtTrait
{
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime", options={"format"="long_timestamp"})
     */
    private $updatedAt;

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): NodeInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
