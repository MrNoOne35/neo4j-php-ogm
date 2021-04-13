<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Hydrator;

use Doctrine\Common\Collections\ArrayCollection;
use Neo4j\OGM\Common\Direction;
use Neo4j\OGM\Converter\ConverterBag;
use Neo4j\OGM\Event\NodeLoadedEvent;
use Neo4j\OGM\Metadata\ClassMetadata;
use Neo4j\OGM\Metadata\EntityMetadata;
use Neo4j\OGM\Metadata\FetchType;
use Neo4j\OGM\Metadata\RelationMetadata;
use Neo4j\OGM\Metadata\RelationshipMetadata;
use Neo4j\OGM\Model\NodeInterface;
use Neo4j\OGM\NodeManagerInterface;
use Neo4j\OGM\Proxy\CollectionProxy;
use Neo4j\OGM\Proxy\ProxyFactoryInterface;

class Hydrator implements HydratorInterface
{
    protected ProxyFactoryInterface $proxyFactory;

    public function __construct(ProxyFactoryInterface $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }

    public function popuplate(NodeManagerInterface $nm, NodeInterface $node, array $values): void
    {
        $classMetadata = $nm->getMetadataCache()->getClassMetadata(get_class($node));
        $this->popuplateProperties($classMetadata, $values, $node);
        // We put it in the cache right away in case another relation references it
        $nm->getNodesCache()->put($classMetadata->getName(), $classMetadata->getIdValue($node), $node);

        if ($classMetadata instanceof EntityMetadata) {
            $this->popuplateQueryResults($classMetadata, $values, $node);
            $this->popuplateRelations($nm, $classMetadata, $values, $node);
        } elseif ($classMetadata instanceof RelationshipMetadata) {
            $this->popuplateRelationship($nm, $classMetadata, $values, $node);
        }
        $nm->getEventDispatcher()->dispatch(new NodeLoadedEvent($node));
    }

    protected function popuplateProperties(ClassMetadata $classMetadata, array $values, NodeInterface $node): void
    {
        if (!isset($values['id'])) {
            throw new \LogicException(sprintf('No id was returned for %s', get_class($node)));
        }
        foreach ($classMetadata->getPropertiesMetadata() as $field => $meta) {
            $fieldKey = $field;
            if ($meta->hasCustomKey()) {
                $fieldKey = $meta->getKey();
            }

            if (!array_key_exists($fieldKey, $values)) {
                continue;
            }
            $v = $values[$fieldKey];

            if ($meta->hasConverter()) {
                $converter = ConverterBag::get($meta->getConverterType());
                $v = $converter->toPHPValue($values, $field, $meta->getConverterOptions());
            }
            $meta->setValue($node, $v);
        }
        $classMetadata->setIdValue($node, $values['id']);
    }

    protected function popuplateQueryResults(EntityMetadata $classMetadata, array $values, NodeInterface $node): void
    {
        foreach ($classMetadata->getQueryResultsMetadata() as $field => $meta) {
            if (!isset($values[$field])) {
                continue;
            }
            $meta->setValue($node, $values[$field]);
        }
    }

    protected function popuplateRelations(NodeManagerInterface $nm, EntityMetadata $classMetadata, array $values, NodeInterface $node): void
    {
        foreach ($classMetadata->getRelationsMetadata() as $field => $relationMetadata) {
            if (!array_key_exists($field, $values)) {
                continue;
            }

            if ($relationMetadata->isCollection()
                && FetchType::EXTRA_LAZY === $relationMetadata->getFetch()
                && null === $values[$field]) {
                // We need to create a collection proxy for this field
                $relationMetadata->setValue($node, new CollectionProxy(
                    $nm,
                    $node,
                    $field,
                    $field,
                ));
                if ($relationMetadata->getRelationshipPropertyName()) {
                    $relationMetadata->setRelationshipValue($node, new CollectionProxy(
                        $nm,
                        $node,
                        $field,
                        $relationMetadata->getRelationshipPropertyName(),
                    ));
                }

                continue;
            }

            if (!is_array($values[$field])) {
                continue;
            }

            if ($relationMetadata->isCollection()) {
                if (!count($values[$field])) {
                    continue;
                }
            }

            $relationshipClassName = $relationMetadata->getRelationship();
            $relationshipMetadata = $nm->getMetadataCache()->getRelationshipClassMetadata($relationshipClassName);

            switch ($relationMetadata->getDirection()) {
                case Direction::OUTGOING:
                    $targetRelationClassName = $relationshipMetadata->getEndClassName();

                    break;

                case Direction::INCOMING:
                    $targetRelationClassName = $relationshipMetadata->getStartClassName();

                    break;

                case Direction::BOTH:
                    if ($relationshipMetadata->getStartClassName() === $classMetadata->getName()) {
                        $targetRelationClassName = $relationshipMetadata->getEndClassName();
                    } else {
                        $targetRelationClassName = $relationshipMetadata->getStartClassName();
                    }

                    break;
            }
            $targetRelationMetadata = $nm->getMetadataCache()->getEntityClassMetadata($targetRelationClassName);

            if ($relationMetadata->isCollection()) {
                $relations = new ArrayCollection();
                $meta = new ArrayCollection();
                foreach ($values[$field] as $item) {
                    $relationInfo = $this->handleRelation(
                        $nm,
                        $relationMetadata,
                        $relationshipMetadata,
                        $item,
                        $targetRelationMetadata,
                    );
                    if (null !== $relationInfo) {
                        $relations->add($relationInfo['relation']);
                        if (isset($relationInfo['meta'])) {
                            $meta->add($relationInfo['meta']);
                        }
                    }
                }
                $relationMetadata->setValue($node, $relations);
                if ($relationMetadata->getRelationshipPropertyName()) {
                    $relationMetadata->setRelationshipValue($node, $meta);
                }
            } else {
                $relationInfo = $this->handleRelation(
                    $nm,
                    $relationMetadata,
                    $relationshipMetadata,
                    $values[$field],
                    $targetRelationMetadata,
                );
                $relationMetadata->setValue($node, $relationInfo ? $relationInfo['relation'] : null);
                if ($relationMetadata->getRelationshipPropertyName()) {
                    $relationMetadata->setRelationshipValue($node, $relationInfo ? $relationInfo['meta'] : null);
                }
            }
        }
    }

    protected function handleRelation(
        NodeManagerInterface $nm,
        RelationMetadata $relationMetadata,
        ClassMetadata $relationshipMetadata,
        array $relationValues,
        ClassMetadata $targetRelationMetadata
    ): ?array {
        if (empty($relationValues['entity'])
            || !isset($relationValues['entity']['id'])) {
            return null;
        }

        $relationInfo = [];

        $isLazy = !empty($relationValues['lazy']);
        $relationInfo['relation'] = $this->instanciateAndPopulate($nm, $targetRelationMetadata, $relationValues['entity'], $isLazy);
        if ($relationMetadata->getRelationshipPropertyName() && isset($relationValues['meta']['id'])) {
            $relationInfo['meta'] = $this->instanciateAndPopulate($nm, $relationshipMetadata, $relationValues['meta'], $isLazy);
        } else {
            $relationInfo['meta'] = null;
        }

        return $relationInfo;
    }

    protected function instanciateAndPopulate(NodeManagerInterface $nm, ClassMetadata $classMetadata, array $values, bool $isLazy): NodeInterface
    {
        $node = $nm->getNodesCache()->get($classMetadata->getName(), $values['id']);
        if (!$node) {
            if (!$isLazy || !$classMetadata->hasFields()) {
                $node = $classMetadata->newInstance();
            } else {
                $node = $this->proxyFactory->getInstance($nm, $classMetadata, array_keys($values));
            }
        }
        $this->popuplate($nm, $node, $values);

        return $node;
    }

    protected function popuplateRelationship(NodeManagerInterface $nm, RelationshipMetadata $classMetadata, array $values, NodeInterface $node): void
    {
        if (isset($values[$classMetadata->getStartKey()])) {
            $id = $values[$classMetadata->getStartKey()];
            $startClassMetadata = $nm->getMetadataCache()->getEntityClassMetadata($classMetadata->getStartClassName());
            $relation = $this->instanciateAndPopulate($nm, $startClassMetadata, ['id' => $id], true);
            $classMetadata->setStartEntity($node, $relation);
        }
        if (isset($values[$classMetadata->getEndKey()])) {
            $id = $values[$classMetadata->getEndKey()];
            $endClassMetadata = $nm->getMetadataCache()->getEntityClassMetadata($classMetadata->getEndClassName());
            $relation = $this->instanciateAndPopulate($nm, $endClassMetadata, ['id' => $id], true);
            $classMetadata->setEndEntity($node, $relation);
        }
    }
}
