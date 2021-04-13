<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Tests;

use Psr\EventDispatcher\EventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    private $events = [];

    public function dispatch(object $event)
    {
        $this->events[] = $event;
    }

    public function clear()
    {
        $this->events = [];
    }

    public function getEvent()
    {
        return $this->events;
    }
}
