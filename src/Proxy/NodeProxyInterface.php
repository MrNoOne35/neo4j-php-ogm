<?php

/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Proxy;

use Neo4j\OGM\Model\NodeInterface;
use Neo4j\OGM\NodeManagerInterface;

interface NodeProxyInterface extends NodeInterface
{
    public function __proxySetClassName(string $className): void;

    public function __proxySetNodeManager(NodeManagerInterface $nm): void;

    public function __proxySetInitialized(string $propertyName): void;
}
