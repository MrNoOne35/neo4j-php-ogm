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

use Neo4j\OGM\NodeManagerInterface;

trait NodeProxyTrait
{
    private array $__proxyInitialized = [];
    private bool $__proxyisFetched = false;
    private NodeManagerInterface $__proxyNodeManager;
    private string $className;

    public function __proxySetClassName(string $className): void
    {
        $this->className = $className;
    }

    public function __proxySetInitialized(string $propertyName): void
    {
        $this->initialized[] = $propertyName;
    }

    public function __proxySetNodeManager(NodeManagerInterface $nm): void
    {
        $this->__proxyNodeManager = $nm;
    }

    private function __proxyGetProperty(string $property, string $getter)
    {
        if (!in_array($property, $this->__proxyInitialized) && !$this->__proxyisFetched) {
            $repository = $this->__proxyNodeManager->getRepository($this->className);
            $repository->reload($this);
        }

        return parent::$getter();
    }
}
