<?php

/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Exception;

/**
 * Contains exception messages for all invalid lifecycle state exceptions inside UnitOfWork.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @credit Benjamin Eberlei <kontakt@beberlei.de>
 */
class OGMInvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param object $node
     *
     * @return OGMInvalidArgumentException
     */
    public static function entityNotManaged($node)
    {
        return new self('Entity '.self::objectToString($node).' is not managed. An entity is managed if its fetched '.
            'from the database or registered as new through NodeManager#persist');
    }

    /**
     * Helper method to show an object as string.
     *
     * @param object $obj
     *
     * @return string
     */
    private static function objectToString($obj)
    {
        return method_exists($obj, '__toString') ? (string) $obj : get_class($obj).'@'.spl_object_hash($obj);
    }
}
