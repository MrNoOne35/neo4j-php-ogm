<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Metadata\Cache;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Neo4j\OGM\Metadata\ClassMetadata;
use Neo4j\OGM\Metadata\EntityMetadata;
use Neo4j\OGM\Metadata\Factory\NodeAnnotationMetadataFactory;
use Neo4j\OGM\Metadata\Factory\NodeAnnotationMetadataFactoryInterface;
use Neo4j\OGM\Metadata\RelationshipMetadata;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class MetadataCache implements MetadataCacheInterface
{
    protected ?NodeAnnotationMetadataFactoryInterface $metadataFactory;

    /**
     * @var EntityMetadata[]
     */
    protected array $loadedMetadata = [];

    public function __construct(?string $tmpDir = null)
    {
        $cachePool = new FilesystemAdapter('', 0, ($tmpDir ?? sys_get_temp_dir()).DIRECTORY_SEPARATOR.'neo4j');
        $cache = DoctrineProvider::wrap($cachePool);
        //$cache = new FilesystemCache(($tmpDir ?? sys_get_temp_dir()).DIRECTORY_SEPARATOR.'neo4j');
        $reader = new CachedReader(new AnnotationReader(), $cache, true);
        $this->metadataFactory = new NodeAnnotationMetadataFactory($reader);
    }

    public function getClassMetadata(string $className): ClassMetadata
    {
        $classMetadata = $this->getClassMetadataCached($className);
        if (!($classMetadata instanceof EntityMetadata) && !($classMetadata instanceof RelationshipMetadata)) {
            throw new \LogicException(sprintf('Unhandled node meta type %s', get_class($classMetadata)));
        }

        return $classMetadata;
    }

    public function getRelationshipClassMetadata(string $className): RelationshipMetadata
    {
        $classMetadata = $this->getClassMetadataCached($className);
        if (!($classMetadata instanceof RelationshipMetadata)) {
            throw new \LogicException(sprintf('Bad node type %s, expected a %s', $className, RelationshipMetadata::class));
        }

        return $classMetadata;
    }

    public function getEntityClassMetadata(string $className): EntityMetadata
    {
        $classMetadata = $this->getClassMetadataCached($className);
        if (!($classMetadata instanceof EntityMetadata)) {
            throw new \LogicException(sprintf('Bad node type %s, expected a %s', $className, EntityMetadata::class));
        }

        return $classMetadata;
    }

    public function getClassMetadataCached(string $className): EntityMetadata
    {
        if (!array_key_exists($className, $this->loadedMetadata)) {
            $classMetadata = $this->metadataFactory->create($className);
            if ($classMetadata->getName() !== $className) {
                if (array_key_exists($classMetadata->getName(), $this->loadedMetadata)) {
                    $this->loadedMetadata[$className] = $this->loadedMetadata[$classMetadata->getName()];
                } else {
                    $this->loadedMetadata[$classMetadata->getName()] = $classMetadata;
                    $this->loadedMetadata[$className] = $classMetadata;
                }
            } else {
                $this->loadedMetadata[$className] = $classMetadata;
            }

            return $classMetadata;
        }

        return $this->loadedMetadata[$className];
    }
}
