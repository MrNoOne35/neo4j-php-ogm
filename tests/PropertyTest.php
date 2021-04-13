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

use Neo4j\OGM\Tests\Entity\PropertyTest\TestEntity;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
final class PropertyTest extends Base
{
    /**
     * @before
     */
    public function reset()
    {
        $this->nm->getClient()->run('MATCH (n) DETACH DELETE n');
    }

    public function testWrongTypeOnNullable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $entity = new TestEntity();
        $entity->setName('hello');
        $entity->setNameNullable(false);
        $this->nm->getRepository(TestEntity::class)->save($entity);
    }

    public function testWringTypeOnNotNullable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $entity = new TestEntity();
        $entity->setName(false);
        $this->nm->getRepository(TestEntity::class)->save($entity);
    }

    public function testNullOnNotNullable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $entity = new TestEntity();
        $entity->setName(null);
        $this->nm->getRepository(TestEntity::class)->save($entity);
    }

    public function testValid(): void
    {
        $this->expectNotToPerformAssertions(\InvalidArgumentException::class);
        $entity = new TestEntity();
        $entity->setName('hello');
        $this->nm->getRepository(TestEntity::class)->save($entity);

        $entity = new TestEntity();
        $entity->setName('hello');
        $entity->setNameNullable('world');
        $this->nm->getRepository(TestEntity::class)->save($entity);
    }
}
