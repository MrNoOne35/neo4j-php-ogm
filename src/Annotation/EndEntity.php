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
class EndEntity
{
    /**
     * @var string
     */
    public $target;
}
