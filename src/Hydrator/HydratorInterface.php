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

use Neo4j\OGM\Model\NodeInterface;
use Neo4j\OGM\NodeManagerInterface;

interface HydratorInterface
{
    public function popuplate(NodeManagerInterface $nm, NodeInterface $node, array $values): void;
}
