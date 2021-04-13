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

use Doctrine\Common\Collections\Criteria;
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
final class QueryBuilderTest extends Base
{
    /**
     * @before
     */
    public function reset()
    {
        $this->nm->getClient()->run('MATCH (n) DETACH DELETE n');
    }

    public function testCreateEntity(): void
    {
        $child = new TestEntity();
        $child->setName(TestEntity::class);

        $statement = $this->nm->getQueryBuilder()->getCreateQuery($child, 'entity');

        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertEquals(1, count($result));
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('entity_id'));
    }

    public function testRelationshipBothLazy(): void
    {
        $parent = new BothLazy_TestParent();
        $parent->setName(BothLazy_TestParent::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($parent, 'parent');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('parent_id'));
        $pid = $result->first()->get('parent_id');
        $parent->setId($pid);

        $child = new BothLazy_TestChild();
        $child->setName(BothLazy_TestChild::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($child, 'child');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('child_id'));
        $cid = $result->first()->get('child_id');
        $child->setId($cid);

        $relationship = new BothLazy_TestBelongsTo();
        $relationship->setFrom($child);
        $relationship->setTo($parent);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($relationship, 'relationship');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('relationship_id'));
        $rid = $result->first()->get('relationship_id');

        $statement = $this->nm->getQueryBuilder()->getSearchQuery(BothLazy_TestChild::class, 'test_relationship', $this->buildCriteria(['id()' => $cid]));
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertEquals(1, count($result));
        $item = $result->first();
        $values = $item->get('test_relationship_value');
        $this->assertNotNull($values);

        $this->checkLazy($values, $pid, $cid, BothLazy_TestChild::class, $rid, true);
    }

    public function testRelationshipChildLazy(): void
    {
        $parent = new ChildLazy_TestParent();
        $parent->setName(ChildLazy_TestParent::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($parent, 'parent');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('parent_id'));
        $pid = $result->first()->get('parent_id');
        $parent->setId($pid);

        $child = new ChildLazy_TestChild();
        $child->setName(ChildLazy_TestChild::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($child, 'child');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('child_id'));
        $cid = $result->first()->get('child_id');
        $child->setId($cid);

        $relationship = new ChildLazy_TestBelongsTo();
        $relationship->setFrom($child);
        $relationship->setTo($parent);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($relationship, 'relationship');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('relationship_id'));
        $rid = $result->first()->get('relationship_id');

        $statement = $this->nm->getQueryBuilder()->getSearchQuery(ChildLazy_TestChild::class, 'test_relationship', $this->buildCriteria(['id()' => $cid]));
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertEquals(1, count($result));
        $item = $result->first();
        $values = $item->get('test_relationship_value');
        $this->assertNotNull($values);

        $this->checkLazy($values, $pid, $cid, ChildLazy_TestChild::class, $rid, false);
    }

    public function testRelationshipParentLazy(): void
    {
        $parent = new ParentLazy_TestParent();
        $parent->setName(ParentLazy_TestParent::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($parent, 'parent');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('parent_id'));
        $pid = $result->first()->get('parent_id');
        $parent->setId($pid);

        $child = new ParentLazy_TestChild();
        $child->setName(ParentLazy_TestChild::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($child, 'child');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('child_id'));
        $cid = $result->first()->get('child_id');
        $child->setId($cid);

        $relationship = new ParentLazy_TestBelongsTo();
        $relationship->setFrom($child);
        $relationship->setTo($parent);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($relationship, 'relationship');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('relationship_id'));
        $rid = $result->first()->get('relationship_id');

        $statement = $this->nm->getQueryBuilder()->getSearchQuery(ParentLazy_TestChild::class, 'test_relationship', $this->buildCriteria(['id()' => $cid]));
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertEquals(1, count($result));
        $item = $result->first();
        $values = $item->get('test_relationship_value');
        $this->assertNotNull($values);

        $this->checkNotLazy($values, $pid, ParentLazy_TestParent::class, $cid, ParentLazy_TestChild::class, $rid, false);
    }

    public function testRelationshipNoneLazy(): void
    {
        $parent = new NoneLazy_TestParent();
        $parent->setName(NoneLazy_TestParent::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($parent, 'parent');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('parent_id'));
        $pid = $result->first()->get('parent_id');
        $parent->setId($pid);

        $child = new NoneLazy_TestChild();
        $child->setName(NoneLazy_TestChild::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($child, 'child');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('child_id'));
        $cid = $result->first()->get('child_id');
        $child->setId($cid);

        $relationship = new NoneLazy_TestBelongsTo();
        $relationship->setFrom($child);
        $relationship->setTo($parent);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($relationship, 'relationship');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('relationship_id'));
        $rid = $result->first()->get('relationship_id');

        $statement = $this->nm->getQueryBuilder()->getSearchQuery(NoneLazy_TestChild::class, 'test_relationship', $this->buildCriteria(['id()' => $cid]));
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertEquals(1, count($result));
        $item = $result->first();
        $values = $item->get('test_relationship_value');
        $this->assertNotNull($values);

        $this->checkNotLazy($values, $pid, NoneLazy_TestParent::class, $cid, NoneLazy_TestChild::class, $rid, false);
    }

    public function testComplexQueries(): void
    {
        $repository = $this->nm->getRepository(TestEntity::class);

        $entity1 = new TestEntity();
        $entity1->setName('Entity1');
        $repository->save($entity1);

        $entity2 = new TestEntity();
        $entity2->setName('Entity2');
        $repository->save($entity2);

        $criteria = new Criteria();
        $expressionBuilder = Criteria::expr();
        $criteria->andWhere(
            $expressionBuilder->eq('name', 'Entity1')
        );
        $result = $repository->matching($criteria);
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        $criteria = new Criteria();
        $expressionBuilder = Criteria::expr();
        $criteria
            ->andWhere(
                $expressionBuilder->eq('name', 'Entity1'),
            )
            ->andWhere(
                $expressionBuilder->eq('name', 'Entity2'),
            )
        ;
        $result = $repository->matching($criteria);
        $this->assertNotNull($result);
        $this->assertEquals(0, count($result));

        $criteria = new Criteria();
        $expressionBuilder = Criteria::expr();
        $criteria
            ->orWhere(
                $expressionBuilder->eq('name', 'Entity1'),
            )
            ->orWhere(
                $expressionBuilder->eq('name', 'Entity2'),
            )
        ;
        $result = $repository->matching($criteria);
        $this->assertNotNull($result);
        $this->assertEquals(2, count($result));

        $criteria = new Criteria();
        $expressionBuilder = Criteria::expr();
        $criteria->andWhere(
            $expressionBuilder->in('name', ['Entity1', 'Entity2']),
        );
        $result = $repository->matching($criteria);
        $this->assertNotNull($result);
        $this->assertEquals(2, count($result));

        $criteria = new Criteria();
        $expressionBuilder = Criteria::expr();
        $criteria->andWhere(
            $expressionBuilder->in('id()', [$entity1->getId(), $entity2->getId()]),
        );
        $result = $repository->matching($criteria);
        $this->assertNotNull($result);
        $this->assertEquals(2, count($result));

        $criteria = new Criteria();
        $expressionBuilder = Criteria::expr();
        $criteria->andWhere(
            $expressionBuilder->startsWith('name', 'Entity'),
        );
        $result = $repository->matching($criteria);
        $this->assertNotNull($result);
        $this->assertEquals(2, count($result));

        $criteria = new Criteria();
        $expressionBuilder = Criteria::expr();
        $criteria
            ->andWhere(
                $expressionBuilder->startsWith('name', 'Entity'),
            )
            ->andWhere(
                $expressionBuilder->orX(
                    $expressionBuilder->eq('id()', $entity1->getId()),
                    $expressionBuilder->eq('id()', $entity2->getId()),
                ),
            )
        ;

        $result = $repository->matching($criteria);
        $this->assertNotNull($result);
        $this->assertEquals(2, count($result));

        $criteria = new Criteria();
        $expressionBuilder = Criteria::expr();
        $criteria->andWhere(
            $expressionBuilder->notIn('name', ['Entity1']),
        );
        $result = $repository->matching($criteria);
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));
    }

    private function checkLazy(array $values, int $pid, int $cid, string $cClassName, int $rid, bool $withMeta): void
    {
        $this->assertArrayHasKey('name', $values);
        $this->assertEquals($cClassName, $values['name']);

        $this->assertArrayHasKey('id', $values);
        $this->assertEquals($cid, $values['id']);

        $this->assertArrayHasKey('parent', $values);
        $this->assertEquals(1, $values['parent']['lazy']);
        $this->assertArrayHasKey('entity', $values['parent']);
        $this->assertArrayHasKey('id', $values['parent']['entity']);
        $this->assertEquals($pid, $values['parent']['entity']['id']);

        if ($withMeta) {
            $this->assertArrayHasKey('meta', $values['parent']);

            $this->assertArrayHasKey('id', $values['parent']['meta']);
            $this->assertEquals($rid, $values['parent']['meta']['id']);

            $this->assertArrayHasKey('from', $values['parent']['meta']);
            $this->assertEquals($cid, $values['parent']['meta']['from']);

            $this->assertArrayHasKey('to', $values['parent']['meta']);
            $this->assertEquals($pid, $values['parent']['meta']['to']);
        } else {
            $this->assertArrayNotHasKey('meta', $values['parent']);
        }
    }

    private function checkNotLazy(array $values, int $pid, string $pClassName, int $cid, string $cClassName, int $rid, bool $withMeta): void
    {
        $this->assertArrayHasKey('name', $values);
        $this->assertEquals($cClassName, $values['name']);

        $this->assertArrayHasKey('id', $values);
        $this->assertEquals($cid, $values['id']);

        $this->assertArrayHasKey('parent', $values);

        $this->assertArrayHasKey('lazy', $values['parent']);
        $this->assertEquals(0, $values['parent']['lazy']);

        $this->assertArrayHasKey('entity', $values['parent']);
        $this->assertArrayHasKey('id', $values['parent']['entity']);
        $this->assertEquals($pid, $values['parent']['entity']['id']);

        $this->assertArrayHasKey('name', $values['parent']['entity']);
        $this->assertEquals($pClassName, $values['parent']['entity']['name']);

        $this->assertArrayHasKey('child', $values['parent']['entity']);
        $this->assertArrayHasKey('lazy', $values['parent']['entity']['child']);
        $this->assertEquals(1, $values['parent']['entity']['child']['lazy']);
        $this->assertArrayHasKey('entity', $values['parent']['entity']['child']);
        $this->assertArrayHasKey('id', $values['parent']['entity']['child']['entity']);
        $this->assertEquals($cid, $values['parent']['entity']['child']['entity']['id']);

        if ($withMeta) {
            $this->assertArrayHasKey('meta', $values['parent']);
            $this->assertArrayHasKey('id', $values['parent']['meta']);
            $this->assertEquals($rid, $values['parent']['meta']['id']);

            $this->assertArrayHasKey('from', $values['parent']['meta']);
            $this->assertEquals($cid, $values['parent']['meta']['from']);

            $this->assertArrayHasKey('to', $values['parent']['meta']);
            $this->assertEquals($pid, $values['parent']['meta']['to']);

            $this->assertArrayHasKey('meta', $values['parent']['entity']['child']);
            $this->assertArrayHasKey('id', $values['parent']['entity']['child']['meta']);
            $this->assertEquals($rid, $values['parent']['entity']['child']['meta']['id']);

            $this->assertArrayHasKey('from', $values['parent']['entity']['child']['meta']);
            $this->assertEquals($cid, $values['parent']['entity']['child']['meta']['from']);

            $this->assertArrayHasKey('to', $values['parent']['entity']['child']['meta']);
            $this->assertEquals($pid, $values['parent']['entity']['child']['meta']['to']);
        } else {
            $this->assertArrayNotHasKey('meta', $values['parent']['entity']['child']);
        }
    }
}
