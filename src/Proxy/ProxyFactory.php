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
use Neo4j\OGM\Metadata\EntityMetadata;
use Neo4j\OGM\Metadata\RelationshipMetadata;
use Neo4j\OGM\NodeManagerInterface;

class ProxyFactory implements ProxyFactoryInterface
{
    protected string $proxyDir;

    public function __construct(?string $tmpDir = null)
    {
        $this->proxyDir = ($tmpDir ?? sys_get_temp_dir()).DIRECTORY_SEPARATOR.'neo4j-proxy';
    }

    public function getInstance(NodeManagerInterface $nm, ClassMetadata $classMetadata, array $knownProperties): NodeProxyInterface
    {
        $object = $this->getProxy($classMetadata);
        $object->__proxySetClassName($classMetadata->getName());
        $object->__proxySetNodeManager($nm);
        foreach ($knownProperties as $knownProperty) {
            $object->__proxySetInitialized($knownProperty);
        }

        return $object;
    }

    protected function getProxy(ClassMetadata $classMetadata): NodeProxyInterface
    {
        $proxyClass = $this->getProxyClass($classMetadata);
        $proxyFile = $this->proxyDir.DIRECTORY_SEPARATOR.$proxyClass.'.php';

        if (!class_exists($proxyClass)) {
            if ($this->needsProxyCreation($classMetadata, $proxyFile)) {
                $this->createProxy($classMetadata);
            }
            // using locks to avoid reading a partially written file
            $fo = fopen($proxyFile, 'r');
            flock($fo, LOCK_SH);

            require $proxyFile;
            flock($fo, LOCK_UN);
            fclose($fo);
        }

        return $this->newProxyInstance($proxyClass);
    }

    protected function needsProxyCreation(ClassMetadata $classMetadata, string $proxyFile): bool
    {
        if (!@file_exists($proxyFile)) {
            return true;
        }
        $classFilename = $classMetadata->getReflectionClass()->getFileName();
        if (filemtime($classFilename) > filemtime($proxyFile)) {
            return true;
        }

        return false;
    }

    protected function createProxy(ClassMetadata $classMetadata): void
    {
        if ($classMetadata->getReflectionClass()->isFinal()) {
            throw new \InvalidArgumentException(sprintf('Class "%s" cannot be final', $classMetadata->getName()));
        }

        if ($classMetadata->getReflectionClass()->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Class "%s" cannot be abstract', $classMetadata->getName()));
        }

        $className = $classMetadata->getName();
        $proxyClass = $this->getProxyClass($classMetadata);
        $proxyFile = $this->proxyDir.'/'.$proxyClass.'.php';
        $methodProxies = $this->getMethodProxies($classMetadata);

        $content = <<<PROXY
<?php

use Neo4j\\OGM\\Proxy\\NodeProxyInterface;
use Neo4j\\OGM\\Proxy\\NodeProxyTrait;

class {$proxyClass} extends {$className} implements NodeProxyInterface
{
    use NodeProxyTrait;
    {$methodProxies}
}

PROXY;

        $this->checkProxyDirectory();

        file_put_contents($proxyFile, $content, LOCK_EX);
    }

    protected function getMethodProxies(ClassMetadata $classMetadata)
    {
        $proxies = '';
        $properties = array_keys($classMetadata->getPropertiesMetadata());
        if ($classMetadata instanceof EntityMetadata) {
            $properties = array_merge(
                $properties,
                array_keys($classMetadata->getRelationsMetadata()),
                array_keys($classMetadata->getQueryResultsMetadata()),
            );
        } elseif ($classMetadata instanceof RelationshipMetadata) {
            $properties[] = $classMetadata->getStartKey();
            $properties[] = $classMetadata->getEndKey();
        }

        foreach ($properties as $property) {
            $parentGetter = 'get'.ucfirst($property);
            $getter = $parentGetter.'()';
            $reflClass = $classMetadata->getReflectionClass();
            $g = $parentGetter;
            if ($reflClass->hasMethod($g)) {
                $reflMethod = $reflClass->getMethod($g);
                if ($reflMethod->hasReturnType()) {
                    $rt = $reflMethod->getReturnType();
                    $getter .= ': '.($rt->allowsNull() ? ' ?' : '').ltrim($rt, '?');
                }
            }

            $proxies .= <<<METHOD
public function {$getter}
{
    return \$this->__proxyGetProperty('{$property}', '{$parentGetter}');
}
METHOD;
        }

        return $proxies;
    }

    protected function getProxyClass(ClassMetadata $classMetadata)
    {
        return 'neo4j_ogm_proxy_'.str_replace('\\', '_', $classMetadata->getName());
    }

    private function newProxyInstance($proxyClass)
    {
        static $prototypes = [];

        if (!array_key_exists($proxyClass, $prototypes)) {
            $rc = @unserialize(sprintf('C:%d:"%s":0:{}', strlen($proxyClass), $proxyClass));

            if (false === $rc || $rc instanceof \__PHP_Incomplete_Class) {
                $rc = new \ReflectionClass($proxyClass);

                return $rc->newInstanceWithoutConstructor();
            }

            $prototypes[$proxyClass] = $rc;
        }

        return clone $prototypes[$proxyClass];
    }

    private function checkProxyDirectory()
    {
        if (!is_dir($this->proxyDir)) {
            @mkdir($this->proxyDir);
        }
    }
}
