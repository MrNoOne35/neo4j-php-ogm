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

/**
 * Defines how relations are being fetched.
 */
class FetchType
{
    /**
     * LAZY: Relations are lazy loaded, when it's a collection, the collection is loaded but the entities in it will be lazy loaded on first access.
     */
    const LAZY = 'LAZY';

    /**
     * EXTRA_LAZY: Relations are fully lazy loaded, when it's a collection, it is lazy loaded as well on first access and the entities in it will be lazy loaded on their own first access.
     */
    const EXTRA_LAZY = 'EXTRA_LAZY';

    /**
     * EAGER: Everything is loaded right again.
     */
    const EAGER = 'EAGER';
}
