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

abstract class AbstractQueryAnnotationMetadata
{
    protected ?array $orderBy;

    protected ?int $limit;

    public function __construct(?array $orderBy, ?int $limit)
    {
        $this->orderBy = $orderBy;
        $this->limit = $limit;
    }

    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
