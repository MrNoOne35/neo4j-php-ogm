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

use Neo4j\OGM\Tests\Entity\QueryResult\TestBelongsTo;
use Neo4j\OGM\Tests\Entity\QueryResult\TestChild;
use Neo4j\OGM\Tests\Entity\QueryResult\TestParent;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
final class QueryResultTest extends Base
{
    /**
     * @before
     */
    public function reset()
    {
        $this->nm->getClient()->run('MATCH (n) DETACH DELETE n');
    }

    public function testQueryResult(): void
    {
        $parent = new TestParent();
        $parent->setName(TestParent::class);
        $statement = $this->nm->getQueryBuilder()->getCreateQuery($parent, 'parent');
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertNotNull($result->first());
        $this->assertNotNull($result->first()->get('parent_id'));
        $pid = $result->first()->get('parent_id');
        $parent->setId($pid);

        for ($i = 0; $i < 5; ++$i) {
            $child = new TestChild();
            $child->setName(TestChild::class);
            $statement = $this->nm->getQueryBuilder()->getCreateQuery($child, 'child');
            $result = $this->nm->getClient()->runStatement($statement);
            $this->assertNotNull($result->first());
            $this->assertNotNull($result->first()->get('child_id'));
            $cid = $result->first()->get('child_id');
            $child->setId($cid);

            $relationship = new TestBelongsTo();
            $relationship->setFrom($child);
            $relationship->setTo($parent);
            $statement = $this->nm->getQueryBuilder()->getCreateQuery($relationship, 'relationship');
            $result = $this->nm->getClient()->runStatement($statement);
            $this->assertNotNull($result->first());
            $this->assertNotNull($result->first()->get('relationship_id'));
        }

        $statement = $this->nm->getQueryBuilder()->getSearchQuery(TestParent::class, 'test_query_result', $this->buildCriteria(['id()' => $pid]));
        $result = $this->nm->getClient()->runStatement($statement);
        $this->assertEquals(1, count($result));
        $item = $result->first();
        $values = $item->get('test_query_result_value');
        $this->assertNotNull($values);
        $this->assertEquals(TestParent::class, $values['name']);
        $this->assertEquals($pid, $values['id']);
        $this->assertEquals(5, $values['childrenCount']);
    }
}
