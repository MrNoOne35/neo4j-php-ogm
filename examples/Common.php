<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Examples;

use Laudis\Neo4j\ClientBuilder;
use Neo4j\OGM\Hydrator\Hydrator;
use Neo4j\OGM\Metadata\Cache\MetadataCache;
use Neo4j\OGM\NodeManager;
use Neo4j\OGM\NodeManagerInterface;
use Neo4j\OGM\NodesCache\NodesCache;
use Neo4j\OGM\Proxy\ProxyFactory;
use Neo4j\OGM\QueryBuilder\QueryBuilder;

class Common
{
    public static function getNodeManager(string $boltUrl, string $tempDir): NodeManagerInterface
    {
        $clientBuilder = new ClientBuilder();

        $metadataCache = new MetadataCache($tempDir);

        return new NodeManager(
            $clientBuilder->addBoltConnection(
                'default',
                $boltUrl,
            )->build(),
            $metadataCache,
            new QueryBuilder($metadataCache),
            new Hydrator(
                new ProxyFactory($tempDir)
            ),
            new NodesCache(),
            new EventDispatcher()
        );
    }
}
