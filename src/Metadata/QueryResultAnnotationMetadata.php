<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Metadata;

class QueryResultAnnotationMetadata extends AbstractQueryAnnotationMetadata
{
    protected $query;

    protected $collection;

    public function __construct(string $query, ?array $orderBy, ?int $limit, bool $collection)
    {
        parent::__construct($orderBy, $limit);
        $this->query = $query;
        $this->collection = $collection;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function isCollection(): bool
    {
        return $this->collection;
    }
}
