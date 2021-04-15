<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\QueryBuilder;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;
use Laudis\Neo4j\Databags\Statement;
use Neo4j\OGM\Common\Direction;
use Neo4j\OGM\Converter\ConverterBag;
use Neo4j\OGM\Metadata\Cache\MetadataCacheInterface;
use Neo4j\OGM\Metadata\ClassMetadata;
use Neo4j\OGM\Metadata\EntityMetadata;
use Neo4j\OGM\Metadata\FetchType;
use Neo4j\OGM\Metadata\RelationMetadata;
use Neo4j\OGM\Metadata\RelationshipMetadata;
use Neo4j\OGM\Model\EntityInterface;
use Neo4j\OGM\Model\NodeCreatedAtInterface;
use Neo4j\OGM\Model\NodeInterface;
use Neo4j\OGM\Model\NodeUpdatedAtInterface;
use Neo4j\OGM\Model\RelationshipInterface;

class QueryBuilder implements QueryBuilderInterface
{
    protected MetadataCacheInterface $metadataCache;

    public function __construct(
        MetadataCacheInterface $metadataCache
    ) {
        $this->metadataCache = $metadataCache;
    }

    public function getSearchQuery(string $className, string $identifier, Criteria $criteria): Statement
    {
        $params = [];

        $classMetadata = $this->metadataCache->getClassMetadata($className);
        if (!($classMetadata instanceof EntityMetadata) && !($classMetadata instanceof RelationshipMetadata)) {
            throw new \LogicException(sprintf('Unhandled node meta type %s', get_class($classMetadata)));
        }

        $query = $this->getNodeSearchQueryMatch($classMetadata, $identifier);
        if ($criteria->getWhereExpression()) {
            $query .= PHP_EOL.'WHERE '.$this->getSearchQueryWhere($identifier, $criteria->getWhereExpression(), $params);
        }

        $query .= $this->getCustomSearchQuery($className, $identifier, $params, $criteria->getOrderings(), $criteria->getMaxResults(), $criteria->getFirstResult());

        return Statement::create($query, $params);
    }

    public function getCustomSearchQuery(string $className, string $identifier, array &$params, ?array $orderBy, ?int $limit, ?int $offset): string
    {
        $classMetadata = $this->metadataCache->getClassMetadata($className);
        if (!($classMetadata instanceof EntityMetadata) && !($classMetadata instanceof RelationshipMetadata)) {
            throw new \LogicException(sprintf('Unhandled node meta type %s', get_class($classMetadata)));
        }

        $query = '';
        $extraFields = [];
        if ($classMetadata instanceof EntityMetadata) {
            $knownRelationships = [];
            $query .= $this->getEntityRelationsToFetch(
                $classMetadata,
                $classMetadata->getRelationsMetadata(),
                $identifier,
                $params,
                $extraFields,
                $knownRelationships
            );
            $query .= $this->getEntityQueryResultsToFetch($classMetadata, $identifier, $extraFields);
        }
        $query .= PHP_EOL.'RETURN ';
        if ($classMetadata instanceof EntityMetadata) {
            $query .= $this->getEntityFieldsToHydrate(
                $classMetadata,
                $identifier,
                $extraFields,
                FetchType::EAGER
            );
        } elseif ($classMetadata instanceof RelationshipMetadata) {
            $query .= $this->getRelationshipFieldsToHydrate(
                $classMetadata,
                $identifier,
                $identifier.'_from',
                $identifier.'_to',
                FetchType::EAGER
            );
        }
        $query .= ' AS '.$identifier.'_value';

        $queryExtra = $this->getSearchQueryExtra(
            $orderBy,
            $limit,
            $offset,
            [
                'default' => $identifier,
            ]
        );
        if ($queryExtra) {
            $query .= PHP_EOL.$queryExtra;
        }

        return $query;
    }

    public function getCountQuery(string $className, string $identifier, Criteria $criteria): Statement
    {
        $params = [];

        $classMetadata = $this->metadataCache->getClassMetadata($className);
        if (!($classMetadata instanceof EntityMetadata) && !($classMetadata instanceof RelationshipMetadata)) {
            throw new \LogicException(sprintf('Unhandled node meta type %s', get_class($classMetadata)));
        }

        $query = $this->getNodeSearchQueryMatch($classMetadata, $identifier);
        if ($criteria->getWhereExpression()) {
            $query .= PHP_EOL.'WHERE '.$this->getSearchQueryWhere($identifier, $criteria->getWhereExpression(), $params);
        }
        $query .= PHP_EOL.'RETURN count('.$identifier.') AS '.$identifier.'_value';

        return Statement::create($query, $params);
    }

    public function getCreateQuery(NodeInterface $object, string $identifier): Statement
    {
        if ($object instanceof EntityInterface) {
            return $this->getCreateEntityQuery($object, $identifier);
        }
        if ($object instanceof RelationshipInterface) {
            return $this->getCreateRelationshipQuery($object, $identifier);
        }

        throw new \LogicException(sprintf('Unhandled node type %s', get_class($object)));
    }

    public function getUpdateQuery(NodeInterface $object, string $identifier): ?Statement
    {
        $properties = $this->getPropertiesToSet($object);
        if (empty($properties)) {
            return null;
        }

        $parameters = [
            $identifier.'_properties' => $properties,
        ];
        $query = $this->getNodeSelect($object, $identifier, $parameters);
        $query .= PHP_EOL.'SET '.$identifier.' += $'.$identifier.'_properties';

        return Statement::create($query, $parameters);
    }

    public function getDetachDeleteQuery(NodeInterface $object, string $identifier): Statement
    {
        $parameters = [];
        $query = $this->getNodeSelect($object, $identifier, $parameters);
        $query .= PHP_EOL.'DETACH DELETE '.$identifier;
        $query .= PHP_EOL.'RETURN count('.$identifier.') as ctr';

        return Statement::create($query, $parameters);
    }

    public function getDeleteQuery(NodeInterface $object, string $identifier): Statement
    {
        $parameters = [];
        $query = $this->getNodeSelect($object, $identifier, $parameters);
        $query .= PHP_EOL.'DELETE '.$identifier;
        $query .= PHP_EOL.'RETURN count('.$identifier.') as ctr';

        return Statement::create($query, $parameters);
    }

    public function getLoadRelationQuery(NodeInterface $object, string $identifier, string $field): ?Statement
    {
        $classMetadata = $this->metadataCache->getEntityClassMetadata(get_class($object));

        $allRelations = $classMetadata->getRelationsMetadata();
        if (!isset($allRelations[$field])) {
            return null;
        }
        $relations = [
            $field => $allRelations[$field],
        ];

        $parameters = [];
        $extraFields = [];
        $knownRelationships = [];
        $query = $this->getNodeSelect($object, $identifier, $parameters);
        $query .= $this->getEntityRelationsToFetch(
            $classMetadata,
            $relations,
            $identifier,
            $parameters,
            $extraFields,
            $knownRelationships,
            $relations[$field]->isCollection() ? FetchType::LAZY : FetchType::EAGER,
            ''
        );
        $query .= PHP_EOL.'RETURN {id:id('.$identifier.'),';
        foreach ($extraFields as $field => $path) {
            $values[] = $field.':'.$path;
        }
        $query .= join(',', $values);
        $query .= '} AS '.$identifier.'_value';

        return Statement::create($query, $parameters);
    }

    protected function getCreateRelationshipQuery(RelationshipInterface $object, string $identifier): Statement
    {
        $classMetadata = $this->metadataCache->getRelationshipClassMetadata(get_class($object));
        $startEntity = $classMetadata->getStartEntity($object);
        $endEntity = $classMetadata->getEndEntity($object);
        if (!$startEntity && !$endEntity) {
            throw new \LogicException(sprintf('Both start and end entities must be set for the relationship %s', get_class($object)));
        }

        $startClassMeta = $this->metadataCache->getEntityClassMetadata($classMetadata->getStartClassName());
        $endClassMeta = $this->metadataCache->getEntityClassMetadata($classMetadata->getEndClassName());

        $identifierFrom = $identifier.'_from';
        $identifierTo = $identifier.'_to';

        $properties = $this->getPropertiesToSet($object);

        $query = 'MATCH ('.$identifierFrom.':'.$startClassMeta->getLabel().'), ('.$identifierTo.':'.$endClassMeta->getLabel().')';
        $query .= PHP_EOL.'WHERE id('.$identifierFrom.') = $'.$identifier.'_from_id AND id('.$identifierTo.') = $'.$identifier.'_to_id';
        if ($classMetadata->isUnique()) {
            $query .= ' AND NOT ('.$identifierFrom.')-[:'.$classMetadata->getType().']->('.$identifierTo.')';
        }
        $query .= PHP_EOL.'CREATE ('.$identifierFrom.')-['.$identifier.':'.$classMetadata->getType().']->('.$identifierTo.')';
        if (!empty($properties)) {
            $query .= PHP_EOL.'SET '.$identifier.' += $'.$identifier.'_properties';
        }
        $query .= PHP_EOL.'RETURN id('.$identifier.') AS '.$identifier.'_id';

        $values = [
            $identifier.'_from_id' => $startClassMeta->getIdValue($startEntity),
            $identifier.'_to_id' => $endClassMeta->getIdValue($endEntity),
        ];

        if (!empty($properties)) {
            $values[$identifier.'_properties'] = $properties;
        }

        return Statement::create($query, $values);
    }

    protected function getCreateEntityQuery(EntityInterface $object, string $identifier): Statement
    {
        $properties = $this->getPropertiesToSet($object);

        $classMetadata = $this->metadataCache->getEntityClassMetadata(get_class($object));
        $query = 'CREATE ('.$identifier.':'.$classMetadata->getLabel().')';
        if (!empty($properties)) {
            $query .= PHP_EOL.'SET '.$identifier.' += $'.$identifier.'_properties';
        }
        $query .= PHP_EOL.'RETURN id('.$identifier.') AS '.$identifier.'_id';

        return Statement::create($query, empty($properties) ? null : [$identifier.'_properties' => $this->getPropertiesToSet($object)]);
    }

    protected function getRelationshipSelect(RelationshipInterface $object, string $identifier, array &$params): string
    {
        $classMetadata = $this->metadataCache->getRelationshipClassMetadata(get_class($object));
        $startEntity = $classMetadata->getStartEntity($object);
        $endEntity = $classMetadata->getEndEntity($object);
        if (!$startEntity && !$endEntity) {
            throw new \LogicException(sprintf('Both start and end entities must be set for the relationship %s', get_class($object)));
        }
        $startClassMeta = $this->metadataCache->getEntityClassMetadata($classMetadata->getStartClassName());
        $endClassMeta = $this->metadataCache->getEntityClassMetadata($classMetadata->getEndClassName());

        $identifierFrom = $identifier.'_from';
        $identifierTo = $identifier.'_to';

        $query = 'MATCH ('.$identifierFrom.':'.$startClassMeta->getLabel().')-['.$identifier.':'.$classMetadata->getType().']->('.$identifierTo.':'.$endClassMeta->getLabel().')';
        $query .= PHP_EOL.'WHERE id('.$identifierFrom.') = $'.$identifier.'_from_id AND id('.$identifierTo.') = $'.$identifier.'_to_id';

        $params[$identifier.'_from_id'] = $startClassMeta->getIdValue($startEntity);
        $params[$identifier.'_to_id'] = $endClassMeta->getIdValue($endEntity);

        return $query;
    }

    protected function getNodeSelect(NodeInterface $object, string $identifier, array &$params): string
    {
        if ($object instanceof EntityInterface) {
            return $this->getEntitySelect($object, $identifier, $params);
        }
        if ($object instanceof RelationshipInterface) {
            return $this->getRelationshipSelect($object, $identifier, $params);
        }
    }

    protected function getEntitySelect(EntityInterface $object, string $identifier, array &$params): string
    {
        $classMetadata = $this->metadataCache->getEntityClassMetadata(get_class($object));

        $query = 'MATCH ('.$identifier.':'.$classMetadata->getLabel().')';
        $query .= PHP_EOL.'WHERE id('.$identifier.') = $'.$identifier.'_id';

        $params[$identifier.'_id'] = $classMetadata->getIdValue($object);

        return $query;
    }

    protected function getPropertiesToSet(NodeInterface $object): array
    {
        $classMetadata = $this->metadataCache->getClassMetadata(get_class($object));

        if (null === $classMetadata->getIdValue($object)
            && $object instanceof NodeCreatedAtInterface) {
            $object->setCreatedAt(new \DateTime());
        }
        if ($object instanceof NodeUpdatedAtInterface) {
            $object->setUpdatedAt(new \DateTime());
        }

        $propertyValues = [];
        foreach ($classMetadata->getPropertiesMetadata() as $field => $meta) {
            $fieldKey = $field;

            $v = $meta->getValue($object);
            if (null === $v) {
                if (!$meta->isNullable()) {
                    throw new \InvalidArgumentException(sprintf('%s::%s is null, and nullable is set to false', $classMetadata->getName(), $field));
                }
            } elseif (null !== $meta->getType() && gettype($v) !== $meta->getType()) {
                throw new \InvalidArgumentException(sprintf('%s::%s is of type %s, it should be of type %s', $classMetadata->getName(), $field, gettype($v), $meta->getType()));
            }

            if ($meta->hasCustomKey()) {
                $fieldKey = $meta->getKey();
            }

            if ($meta->hasConverter()) {
                $converter = ConverterBag::get($meta->getConverterType());
                $v = $converter->toDatabaseValue($v, $meta->getConverterOptions());
            }
            $propertyValues[$fieldKey] = $v;
        }

        return $propertyValues;
    }

    protected function getNodeSearchQueryMatch(ClassMetadata $classMetadata, string $identifier): string
    {
        if ($classMetadata instanceof EntityMetadata) {
            return $this->getEntitySearchQueryMatch($classMetadata, $identifier);
        }
        if ($classMetadata instanceof RelationshipMetadata) {
            return $this->getRelationshipSearchQueryMatch($classMetadata, $identifier);
        }

        throw new \LogicException(sprintf('Unhandled node meta type %s', get_class($classMetadata)));
    }

    protected function getEntitySearchQueryMatch(EntityMetadata $classMetadata, string $identifier): string
    {
        return 'MATCH ('.$identifier.':'.$classMetadata->getLabel().')';
    }

    protected function getRelationshipSearchQueryMatch(RelationshipMetadata $classMetadata, string $identifier): string
    {
        $startClassMeta = $this->metadataCache->getEntityClassMetadata($classMetadata->getStartClassName());
        $endClassMeta = $this->metadataCache->getEntityClassMetadata($classMetadata->getEndClassName());
        $identifierFrom = $identifier.'_from';
        $identifierTo = $identifier.'_to';

        return 'MATCH ('.$identifierFrom.':'.$startClassMeta->getLabel().')-['.$identifier.':'.$classMetadata->getType().']->('.$identifierTo.':'.$endClassMeta->getLabel().')';
    }

    protected function getSearchQueryWhere(string $identifier, Expression $expression, array &$params): string
    {
        if ($expression instanceof CompositeExpression) {
            $values = [];
            foreach ($expression->getExpressionList() as $subExpr) {
                $values[] = $this->getSearchQueryWhere($identifier, $subExpr, $params);
            }

            return '('.join(' '.$expression->getType().' ', $values).')';
        }
        if ($expression instanceof Comparison) {
            $name = $expression->getField();

            $operator = $expression->getOperator();
            $value = $expression->getValue();

            $not = false;

            switch ($expression->getOperator()) {
                case Comparison::IS:
                    if (null === $value) {
                        $operator = 'IS NULL';
                    }

                    break;

                case Comparison::NIN:
                    $operator = 'IN';
                    $not = true;

                    break;

                case Comparison::MEMBER_OF:
                    throw new \InvalidArgumentException(sprintf('%s is not supported', Comparison::MEMBER_OF));

                    break;

                case Comparison::STARTS_WITH:
                    $operator = 'STARTS WITH';

                    break;

                case Comparison::ENDS_WITH:
                    $operator = 'ENDS WITH';

                    break;
            }

            if ('id()' === $name) {
                $field = 'id('.$identifier.')';
                $param = $identifier.'_id_val';
            } else {
                $field = $identifier.'.'.$name;
                $param = $name;
            }
            if (null !== $value) {
                $i = 1;
                $baseParam = $param;
                while (array_key_exists($param, $params)) {
                    $param = $baseParam.'_'.$i;
                    ++$i;
                }
                $params[$param] = $value->getValue();
            }
            $query = $field.' '.$operator;
            if ($value) {
                $query .= ' $'.$param;
            }

            if ($not) {
                return 'NOT ('.$query.')';
            }

            return $query;
        }

        return '';
    }

    protected function getSearchQueryExtra(?array $orderBy, ?int $limit, ?int $offset, array $orderByMapping, string $pad = ''): string
    {
        $queryParts = [];
        if (is_array($orderBy) && count($orderBy) > 0) {
            $orderByParts = [];
            $noDefault = array_filter($orderByMapping, function ($key) {
                return 'default' !== $key;
            }, ARRAY_FILTER_USE_KEY);
            $search = array_keys($noDefault);
            $replace = array_values($noDefault);
            foreach ($orderBy as $property => $order) {
                if (strstr($property, '.')) {
                    $orderByParts[] = str_replace($search, $replace, $property)." {$order}";
                } else {
                    $orderByParts[] = $orderByMapping['default'].".{$property} {$order}";
                }
            }
            $queryParts[] = 'ORDER BY '.join(', ', $orderByParts);
        }

        if (is_int($offset) && is_int($limit)) {
            $queryParts[] = sprintf('SKIP %d', $offset);
        }

        if (is_int($limit)) {
            $queryParts[] = sprintf('LIMIT %d', $limit);
        }

        return join(PHP_EOL.$pad, $queryParts);
    }

    /**
     * Get the query part about the relations.
     *
     * @param EntityMetadata     $classMetadata      The entity meta data
     * @param RelationMetadata[] $relations          The list of relations to fetch
     * @param string             $identifier         The source variable of the relations
     * @param string[]           $extraFields        The extra fields to be returned, will be populated with the relations
     * @param string[]           $knownRelationships Internal array to follow up the current relations
     * @param string             $pad                The padding for query formating
     * @param string             $forceFetch         The relations should be fetched using this method
     *
     * @return string The query
     */
    protected function getEntityRelationsToFetch(
        EntityMetadata $classMetadata,
        array $relations,
        string $identifier,
        array &$params,
        array &$extraFields,
        array &$knownRelationships,
        string $forceFetch = '',
        string $pad = ''
    ): string {
        $query = '';
        foreach ($relations as $field => $relationMetadata) {
            $currentKnownRelationships = array_merge($knownRelationships, []);
            $relationshipClassName = $relationMetadata->getRelationship();
            $relationshipId = $relationshipClassName.'-'.$relationMetadata->getDirection();
            if (in_array($relationshipId, $currentKnownRelationships)) {
                continue;
            }
            $currentKnownRelationships[] = $relationshipId;

            if (in_array($relationshipClassName, $currentKnownRelationships)) {
                $fetch = FetchType::LAZY;
            } else {
                $fetch = $forceFetch ? $forceFetch : $relationMetadata->getFetch();
                $currentKnownRelationships[] = $relationshipClassName;
            }
            $query .= $this->getEntityRelationToFetch(
                $classMetadata,
                $identifier,
                $field,
                $relationMetadata,
                $fetch,
                $currentKnownRelationships,
                $params,
                $extraFields,
                $pad
            );
        }

        return $query;
    }

    protected function getEntityRelationToFetch(
        EntityMetadata $classMetadata,
        string $identifier,
        string $field,
        RelationMetadata $relationMetadata,
        string $fetch,
        array $currentKnownRelationships,
        array &$params,
        array &$extraFields,
        string $pad = ''
    ): string {
        $query = '';

        $relationshipClassName = $relationMetadata->getRelationship();
        $relationshipIdentifier = $identifier.'_'.$field.'_meta';
        $relationshipMetadata = $this->metadataCache->getRelationshipClassMetadata($relationshipClassName);
        $relationshipCore = '['.$relationshipIdentifier.':'.$relationshipMetadata->getType().']';

        $connectionEntityIdentifier = $identifier.'_'.$field;

        // When handling an EXTRA_LAZY collection we don't fetch any data!
        if (!$relationMetadata->isCollection() || FetchType::EXTRA_LAZY !== $fetch) {
            switch ($relationMetadata->getDirection()) {
                case Direction::OUTGOING:
                    $connection = '-'.$relationshipCore.'->';
                    $connectionEntityMetadata = $this->metadataCache->getEntityClassMetadata($relationshipMetadata->getEndClassName());
                    $identifierStart = $identifier;
                    $identifierEnd = $connectionEntityIdentifier;

                    break;

                case Direction::INCOMING:
                    $connection = '<-'.$relationshipCore.'-';
                    $connectionEntityMetadata = $this->metadataCache->getEntityClassMetadata($relationshipMetadata->getStartClassName());
                    $identifierStart = $connectionEntityIdentifier;
                    $identifierEnd = $identifier;

                    break;

                case Direction::BOTH:
                    $connection = '<-'.$relationshipCore.'->';
                    $identifierStart = $identifier;
                    if ($relationshipMetadata->getStartClassName() === $classMetadata->getName()) {
                        $connectionEntityMetadata = $this->metadataCache->getEntityClassMetadata($relationshipMetadata->getEndClassName());
                    } else {
                        $connectionEntityMetadata = $this->metadataCache->getEntityClassMetadata($relationshipMetadata->getStartClassName());
                    }
                    $identifierEnd = $connectionEntityIdentifier;

                    break;
            }

            $query .= PHP_EOL.$pad.'CALL {';
            $query .= PHP_EOL.$pad.'  WITH '.$identifier;
            $query .= PHP_EOL.$pad.'  OPTIONAL MATCH ('.$identifier.')'.$connection.'('.$connectionEntityIdentifier.':'.$connectionEntityMetadata->getLabel().')';

            if (!empty($relationMetadata->getFilters())) {
                $query .= PHP_EOL.$pad.'  WHERE ';
                $filters = [];
                foreach ($relationMetadata->getFilters() as $filterField => $value) {
                    if (strstr($filterField, '{RELATIONSHIP}')) {
                        $filterField = str_replace('{RELATIONSHIP}', $relationshipIdentifier, $filterField);
                        $var = $connectionEntityIdentifier.'_'.str_replace('.', '_', $filterField);
                        $filters[] = $filterField.' = $'.$var;
                    } else {
                        $var = $connectionEntityIdentifier.'_'.$filterField;
                        $filters[] = $connectionEntityIdentifier.'.'.$filterField.' = $'.$var;
                    }
                    $params[$var] = $value;
                }
                $query .= join(' AND ', $filters);
            }

            $relationExtraFields = [];
            if (FetchType::EAGER === $fetch) {
                // Push the 2 current variables to make sure they're being preserved
                $relationExtraFields[$identifier.'_tmp'] = $identifier;
                $relationExtraFields[$relationshipIdentifier.'_tmp'] = $relationshipIdentifier;
                $query .= $this->getEntityRelationsToFetch(
                    $connectionEntityMetadata,
                    $connectionEntityMetadata->getRelationsMetadata(),
                    $connectionEntityIdentifier,
                    $params,
                    $relationExtraFields,
                    $currentKnownRelationships,
                    '',
                    $pad.'  '
                );
                unset($relationExtraFields[$identifier.'_tmp'], $relationExtraFields[$relationshipIdentifier.'_tmp']);

                $query .= $this->getEntityQueryResultsToFetch($connectionEntityMetadata, $connectionEntityIdentifier, $relationExtraFields);
            }
            $query .= PHP_EOL.$pad.'  RETURN {';
            $variables = [
                'lazy:'.(FetchType::EAGER === $fetch ? 'false' : 'true'),
                'entity:'.$this->getEntityFieldsToHydrate($connectionEntityMetadata, $connectionEntityIdentifier, $relationExtraFields, $fetch),
            ];
            if ($relationMetadata->getRelationshipPropertyName()) {
                $variables[] = 'meta:'.$this->getRelationshipFieldsToHydrate($relationshipMetadata, $relationshipIdentifier, $identifierStart, $identifierEnd, $fetch);
            }
            $query .= PHP_EOL.$pad.'    '.join(','.PHP_EOL.'    ', $variables);
            $query .= PHP_EOL.$pad.'  } AS '.$connectionEntityIdentifier.'_value';

            $queryExtra = $this->getSearchQueryExtra(
                $relationMetadata->getOrderBy(),
                $relationMetadata->getLimit(),
                null,
                [
                    'default' => $connectionEntityIdentifier,
                    '{RELATIONSHIP}' => $relationshipIdentifier,
                ],
                $pad.'  '
            );
            if ($queryExtra) {
                $query .= PHP_EOL.$queryExtra;
            }

            $query .= PHP_EOL.$pad.'}';
        }

        $query .= PHP_EOL.$pad.'WITH '.$identifier.',';
        if ($extraFields) {
            $query .= join(',', $extraFields).',';
        }
        if ($relationMetadata->isCollection()) {
            if (FetchType::EXTRA_LAZY === $fetch) {
                $query .= 'null AS '.$connectionEntityIdentifier.'_value';
            } else {
                $query .= 'collect('.$connectionEntityIdentifier.'_value) AS '.$connectionEntityIdentifier.'_value';
            }
        } else {
            $query .= $connectionEntityIdentifier.'_value';
        }
        $extraFields[$field] = $connectionEntityIdentifier.'_value';

        return $query;
    }

    protected function getEntityQueryResultsToFetch(EntityMetadata $classMetadata, string $identifier, array &$extraFields): string
    {
        $query = '';
        foreach ($classMetadata->getQueryResultsMetadata() as $field => $queryResultMetadata) {
            $identifierSubQuery = $identifier.'_'.$field.'_value';
            $subQuery = str_replace(['{ENTRY}', '{OUTPUT}'], [$identifier, $identifierSubQuery], $queryResultMetadata->getQuery());
            $queryExtra = $this->getSearchQueryExtra(
                $queryResultMetadata->getOrderBy(),
                $queryResultMetadata->getLimit(),
                null,
                [
                    'default' => $identifierSubQuery,
                ]
            );
            if ($queryExtra) {
                $subQuery .= PHP_EOL.$queryExtra;
            }

            $query .= PHP_EOL.'CALL {';
            $query .= PHP_EOL.'  WITH '.$identifier;
            $query .= PHP_EOL.'  '.$subQuery;
            $query .= PHP_EOL.'}';

            $query .= PHP_EOL.'WITH '.$identifier.',';
            if ($extraFields) {
                $query .= join(',', $extraFields).',';
            }
            if ($queryResultMetadata->isCollection()) {
                $query .= 'collect('.$identifierSubQuery.') as '.$identifierSubQuery;
            } else {
                $query .= $identifierSubQuery;
            }
            $extraFields[$field] = $identifierSubQuery;
        }

        return $query;
    }

    protected function getEntityFieldsToHydrate(EntityMetadata $classMetadata, string $identifier, array $extraFields, string $fetch): string
    {
        $query = ' {';
        $values = [];

        if (FetchType::EAGER === $fetch) {
            foreach ($extraFields as $field => $path) {
                $values[] = $field.':'.$path;
            }
        }

        $fields = $this->getPropertiesToHydrate($classMetadata, $identifier, $fetch);
        foreach ($fields as $name => $source) {
            $values[] = $name.':'.$source;
        }
        $query .= join(',', $values);
        $query .= '}';

        return $query;
    }

    protected function getRelationshipFieldsToHydrate(RelationshipMetadata $classMetadata, string $identifier, string $identifierStart, string $identifierEnd, string $fetch): string
    {
        $fields = $this->getPropertiesToHydrate($classMetadata, $identifier, $fetch);

        $values = [
            $classMetadata->getStartKey().':id('.$identifierStart.')',
            $classMetadata->getEndKey().':id('.$identifierEnd.')',
        ];
        $query = ' {';
        foreach ($fields as $name => $source) {
            $values[] = $name.':'.$source;
        }
        $query .= join(',', $values);
        $query .= '}';

        return $query;
    }

    protected function getPropertiesToHydrate(ClassMetadata $classMetadata, string $identifier, string $fetch): array
    {
        $fields = [
            'id' => 'id('.$identifier.')',
        ];
        if (FetchType::EAGER === $fetch) {
            foreach ($classMetadata->getPropertiesMetadata() as $field => $meta) {
                $fieldKey = $field;

                if ($meta->hasCustomKey()) {
                    $fieldKey = $meta->getKey();
                }
                $fields[$field] = $identifier.'.'.$fieldKey;
            }
        }

        return $fields;
    }
}
