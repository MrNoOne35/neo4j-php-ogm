<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Tests;

use Neo4j\OGM\Tests\Entity\UniqueRelationship\TestChild;
use Neo4j\OGM\Tests\Entity\UniqueRelationship\TestParent;
use Neo4j\OGM\Tests\Entity\UniqueRelationship\TestRelationshipNotUnique;
use Neo4j\OGM\Tests\Entity\UniqueRelationship\TestRelationshipUnique;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
final class UniqueRelationshipTest extends Base
{
    /**
     * @before
     */
    public function reset()
    {
        $this->nm->getClient()->run('MATCH (n) DETACH DELETE n');
    }

    public function testUniqueRelationship(): void
    {
        $parent = new TestParent();
        $this->nm->getRepository(TestParent::class)->save($parent);

        $child = new TestChild();
        $this->nm->getRepository(TestChild::class)->save($child);

        $relationship = new TestRelationshipUnique();
        $relationship->setFrom($child);
        $relationship->setTo($parent);

        $count = $this->nm->getRepository(TestRelationshipUnique::class)->save($relationship);
        $this->assertEquals(1, $count);

        $this->nm->getRepository(TestParent::class)->reload($parent);
        $this->assertEquals(1, $parent->getUniqueCount());

        $relationship = new TestRelationshipUnique();
        $relationship->setFrom($child);
        $relationship->setTo($parent);

        $count = $this->nm->getRepository(TestRelationshipUnique::class)->save($relationship);
        $this->assertEquals(0, $count);

        $this->nm->getRepository(TestParent::class)->reload($parent);
        $this->assertEquals(1, $parent->getUniqueCount());
    }

    public function testNotUniqueRelationship(): void
    {
        $parent = new TestParent();
        $this->nm->getRepository(TestParent::class)->save($parent);

        $child = new TestChild();
        $this->nm->getRepository(TestChild::class)->save($child);

        $relationship = new TestRelationshipNotUnique();
        $relationship->setFrom($child);
        $relationship->setTo($parent);

        $count = $this->nm->getRepository(TestRelationshipNotUnique::class)->save($relationship);
        $this->assertEquals(1, $count);

        $this->nm->getRepository(TestParent::class)->reload($parent);
        $this->assertEquals(1, $parent->getNotUniqueCount());

        $relationship = new TestRelationshipNotUnique();
        $relationship->setFrom($child);
        $relationship->setTo($parent);

        $count = $this->nm->getRepository(TestRelationshipNotUnique::class)->save($relationship);
        $this->assertEquals(1, $count);

        $this->nm->getRepository(TestParent::class)->reload($parent);
        $this->assertEquals(2, $parent->getNotUniqueCount());
    }
}
