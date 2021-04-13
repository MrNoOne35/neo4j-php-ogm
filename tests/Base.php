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

use Doctrine\Common\Collections\Criteria;
use Laudis\Neo4j\ClientBuilder;
use Neo4j\OGM\Hydrator\Hydrator;
use Neo4j\OGM\Metadata\Cache\MetadataCache;
use Neo4j\OGM\NodeManager;
use Neo4j\OGM\NodeManagerInterface;
use Neo4j\OGM\NodesCache\NodesCache;
use Neo4j\OGM\Proxy\ProxyFactory;
use Neo4j\OGM\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
abstract class Base extends TestCase
{
    protected NodeManagerInterface $nm;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $clientBuilder = new ClientBuilder();

        $metadataCache = new MetadataCache(__DIR__.DIRECTORY_SEPARATOR.'var');

        $this->nm = new NodeManager(
            $clientBuilder->addBoltConnection(
                'default',
                getenv('NEO4J_URL'),
            )->build(),
            $metadataCache,
            new QueryBuilder($metadataCache),
            new Hydrator(
                new ProxyFactory(__DIR__.DIRECTORY_SEPARATOR.'var')
            ),
            new NodesCache(),
            new EventDispatcher()
        );
    }

    protected function buildCriteria(array $filters, ?array $orderings = null, $firstResult = null, $maxResults = null): Criteria
    {
        $expressionBuilder = Criteria::expr();
        $criteria = new Criteria(null, $orderings, $firstResult, $maxResults);
        foreach ($filters as $field => $value) {
            $criteria->andWhere($expressionBuilder->eq($field, $value));
        }

        return $criteria;
    }
}
