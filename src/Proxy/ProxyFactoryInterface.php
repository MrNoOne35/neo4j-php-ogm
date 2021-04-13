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

use Neo4j\OGM\Metadata\ClassMetadata;
use Neo4j\OGM\NodeManagerInterface;

interface ProxyFactoryInterface
{
    public function getInstance(NodeManagerInterface $nm, ClassMetadata $classMetadata, array $knownProperties): NodeProxyInterface;
}
