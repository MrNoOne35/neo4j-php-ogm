<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Neo4j\OGM\Tests;

use Neo4j\OGM\Tests\Entity\RelationList\TestBelongsTo;
use Neo4j\OGM\Tests\Entity\RelationList\TestChild;
use Neo4j\OGM\Tests\Entity\RelationList\TestParent;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
final class RelationListTest extends Base
{
    /**
     * @before
     */
    public function reset()
    {
        $this->nm->getClient()->run('MATCH (n) DETACH DELETE n');
    }

    public function testChildren(): void
    {
        $numberOfChildren = 5;

        $parent = new TestParent();
        $parent->setName(TestParent::class);
        $this->nm->getRepository(TestParent::class)->save($parent);

        for ($i = 0; $i < $numberOfChildren; ++$i) {
            $child = new TestChild();
            $child->setName('Child'.$i);
            $this->nm->getRepository(TestChild::class)->save($child);

            $relationship = new TestBelongsTo();
            $relationship->setFrom($child);
            $relationship->setTo($parent);
            $this->nm->getRepository(TestBelongsTo::class)->save($relationship);
        }
        $this->nm->clear();

        /** @var TestParent */
        $loadedParent = $this->nm->getRepository(TestParent::class)->find($parent->getId());
        $this->assertNotNull($loadedParent);
        $this->assertEquals($numberOfChildren, $loadedParent->getChildren()->count());
        for ($i = 0; $i < $numberOfChildren; ++$i) {
            $this->assertTrue($loadedParent === $loadedParent->getChildren()[$i]->getParent());
            $this->assertTrue($loadedParent->getChildren()->exists(function (int $key, TestChild $child) use ($i) {
                return 'Child'.$i === $child->getName();
            }));
        }
    }
}
