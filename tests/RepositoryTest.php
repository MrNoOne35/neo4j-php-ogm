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

use Neo4j\OGM\Proxy\NodeProxyInterface;
use Neo4j\OGM\Tests\Entity\BothLazy\TestBelongsTo as BothLazy_TestBelongsTo;
use Neo4j\OGM\Tests\Entity\BothLazy\TestChild as BothLazy_TestChild;
use Neo4j\OGM\Tests\Entity\BothLazy\TestParent as BothLazy_TestParent;
use Neo4j\OGM\Tests\Entity\ChildLazy\TestBelongsTo as ChildLazy_TestBelongsTo;
use Neo4j\OGM\Tests\Entity\ChildLazy\TestChild as ChildLazy_TestChild;
use Neo4j\OGM\Tests\Entity\ChildLazy\TestParent as ChildLazy_TestParent;
use Neo4j\OGM\Tests\Entity\NoneLazy\TestBelongsTo as NoneLazy_TestBelongsTo;
use Neo4j\OGM\Tests\Entity\NoneLazy\TestChild as NoneLazy_TestChild;
use Neo4j\OGM\Tests\Entity\NoneLazy\TestParent as NoneLazy_TestParent;
use Neo4j\OGM\Tests\Entity\ParentLazy\TestBelongsTo as ParentLazy_TestBelongsTo;
use Neo4j\OGM\Tests\Entity\ParentLazy\TestChild as ParentLazy_TestChild;
use Neo4j\OGM\Tests\Entity\ParentLazy\TestParent as ParentLazy_TestParent;
use Neo4j\OGM\Tests\Entity\TestEntity;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
final class RepositoryTest extends Base
{
    /**
     * @before
     */
    public function reset()
    {
        $this->nm->getClient()->run('MATCH (n) DETACH DELETE n');
    }

    public function testRelationshipBothLazy(): void
    {
        $parent = new BothLazy_TestParent();
        $parent->setName(BothLazy_TestParent::class);
        $this->nm->getRepository(BothLazy_TestParent::class)->save($parent);

        $child = new BothLazy_TestChild();
        $child->setName(BothLazy_TestChild::class);
        $this->nm->getRepository(BothLazy_TestChild::class)->save($child);

        $relationship = new BothLazy_TestBelongsTo();
        $relationship->setFrom($child);
        $relationship->setTo($parent);
        $this->nm->getRepository(BothLazy_TestBelongsTo::class)->save($relationship);

        $this->nm->clear();

        /** @var BothLazy_TestParent */
        $loadedParent = $this->nm->getRepository(BothLazy_TestParent::class)->find($parent->getId());
        $this->assertNotNull($loadedParent);
        $this->assertEquals($parent->getId(), $loadedParent->getId());
        $this->assertFalse($loadedParent instanceof NodeProxyInterface);
        $this->assertEquals($parent->getName(), $loadedParent->getName());

        $this->assertNotNull($loadedParent->getChild());
        $this->assertEquals($child->getId(), $loadedParent->getChild()->getId());
        $this->assertTrue($loadedParent->getChild() instanceof NodeProxyInterface);
        $this->assertEquals($child->getName(), $loadedParent->getChild()->getName());
    }

    public function testRelationshipChildLazy(): void
    {
        $parent = new ChildLazy_TestParent();
        $parent->setName(ChildLazy_TestParent::class);
        $this->nm->getRepository(ChildLazy_TestParent::class)->save($parent);

        $child = new ChildLazy_TestChild();
        $child->setName(ChildLazy_TestChild::class);
        $this->nm->getRepository(ChildLazy_TestChild::class)->save($child);

        $relationship = new ChildLazy_TestBelongsTo();
        $relationship->setFrom($child);
        $relationship->setTo($parent);
        $this->nm->getRepository(ChildLazy_TestBelongsTo::class)->save($relationship);

        $this->nm->clear();

        /** @var ChildLazy_TestParent */
        $loadedParent = $this->nm->getRepository(ChildLazy_TestParent::class)->find($parent->getId());
        $this->assertNotNull($loadedParent);
        $this->assertEquals($parent->getId(), $loadedParent->getId());
        $this->assertFalse($loadedParent instanceof NodeProxyInterface);
        $this->assertEquals($parent->getName(), $loadedParent->getName());

        $this->assertNotNull($loadedParent->getChild());
        $this->assertEquals($child->getId(), $loadedParent->getChild()->getId());
        $this->assertFalse($loadedParent->getChild() instanceof NodeProxyInterface);
        $this->assertEquals($child->getName(), $loadedParent->getChild()->getName());
    }

    public function testRelationshipParentLazy(): void
    {
        $parent = new ParentLazy_TestParent();
        $parent->setName(ParentLazy_TestParent::class);
        $this->nm->getRepository(ParentLazy_TestParent::class)->save($parent);

        $child = new ParentLazy_TestChild();
        $child->setName(ParentLazy_TestChild::class);
        $this->nm->getRepository(ParentLazy_TestChild::class)->save($child);

        $relationship = new ParentLazy_TestBelongsTo();
        $relationship->setFrom($child);
        $relationship->setTo($parent);
        $this->nm->getRepository(ParentLazy_TestBelongsTo::class)->save($relationship);

        $this->nm->clear();

        /** @var ParentLazy_TestParent */
        $loadedParent = $this->nm->getRepository(ParentLazy_TestParent::class)->find($parent->getId());
        $this->assertNotNull($loadedParent);
        $this->assertEquals($parent->getId(), $loadedParent->getId());
        $this->assertFalse($loadedParent instanceof NodeProxyInterface);
        $this->assertEquals($parent->getName(), $loadedParent->getName());

        $this->assertNotNull($loadedParent->getChild());
        $this->assertEquals($child->getId(), $loadedParent->getChild()->getId());
        $this->assertTrue($loadedParent->getChild() instanceof NodeProxyInterface);
        $this->assertEquals($child->getName(), $loadedParent->getChild()->getName());
    }

    public function testRelationshipNoneLazy(): void
    {
        $parent = new NoneLazy_TestParent();
        $parent->setName(NoneLazy_TestParent::class);
        $this->nm->getRepository(NoneLazy_TestParent::class)->save($parent);

        $child = new NoneLazy_TestChild();
        $child->setName(NoneLazy_TestChild::class);
        $this->nm->getRepository(NoneLazy_TestChild::class)->save($child);

        $relationship = new NoneLazy_TestBelongsTo();
        $relationship->setFrom($child);
        $relationship->setTo($parent);
        $this->nm->getRepository(NoneLazy_TestBelongsTo::class)->save($relationship);

        $this->nm->clear();

        /** @var NoneLazy_TestParent */
        $loadedParent = $this->nm->getRepository(NoneLazy_TestParent::class)->find($parent->getId());
        $this->assertNotNull($loadedParent);
        $this->assertEquals($parent->getId(), $loadedParent->getId());
        $this->assertFalse($loadedParent instanceof NodeProxyInterface);
        $this->assertEquals($parent->getName(), $loadedParent->getName());

        $this->assertNotNull($loadedParent->getChild());
        $this->assertEquals($child->getId(), $loadedParent->getChild()->getId());
        $this->assertFalse($loadedParent->getChild() instanceof NodeProxyInterface);
        $this->assertEquals($child->getName(), $loadedParent->getChild()->getName());
    }

    public function testCount(): void
    {
        $entity = new TestEntity();
        $entity->setName('TestEntity1');
        $this->nm->getRepository(TestEntity::class)->save($entity);

        $this->assertEquals(1, $this->nm->getRepository(TestEntity::class)->count([]));

        $this->nm->getRepository(TestEntity::class)->delete($entity);
        $this->assertEquals(0, $this->nm->getRepository(TestEntity::class)->count([]));

        $entity = new TestEntity();
        $entity->setName('TestEntity1');
        $this->nm->getRepository(TestEntity::class)->save($entity);
        $this->assertEquals(1, $this->nm->getRepository(TestEntity::class)->count(['name' => 'TestEntity1']));

        $entity = new TestEntity();
        $entity->setName('TestEntity2');
        $this->nm->getRepository(TestEntity::class)->save($entity);
        $this->assertEquals(1, $this->nm->getRepository(TestEntity::class)->count(['name' => 'TestEntity2']));

        $this->assertEquals(2, $this->nm->getRepository(TestEntity::class)->count([]));
    }
}
