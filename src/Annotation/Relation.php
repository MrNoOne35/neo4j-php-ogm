<?php

/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Relation
{
    public $relationship;

    public $direction;

    public $orderBy;

    public $limit;

    public $collection;

    public $relationshipProperty;

    public $fetch;

    public $filters;
}
