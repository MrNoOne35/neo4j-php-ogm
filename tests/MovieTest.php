<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Neo4j\OGM\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Neo4j\OGM\Examples\Movie\Entity\Movie;
use Neo4j\OGM\Examples\Movie\Entity\Person;
use Neo4j\OGM\Proxy\CollectionProxyInterface;
use Neo4j\OGM\Proxy\NodeProxyInterface;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
final class MovieTest extends Base
{
    /**
     * @before
     */
    public function reset()
    {
        $this->nm->getClient()->run('MATCH (n) DETACH DELETE n');
        $this->nm->getClient()->run(
            file_get_contents(
                __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'examples'.DIRECTORY_SEPARATOR
                .'Movie'.DIRECTORY_SEPARATOR
                .'data'.DIRECTORY_SEPARATOR.'movie.txt'
            )
        );
        $this->nm->clear();
    }

    public function testPerson(): void
    {
        /** @var Person */
        $tomHanks = $this->nm->getRepository(Person::class)->findOneBy(['name' => 'Tom Hanks']);
        $this->assertNotNull($tomHanks);

        $this->assertEquals(12, $tomHanks->getTotalActedIn());

        $this->assertInstanceOf(CollectionProxyInterface::class, $tomHanks->getLastestActedIn());
        $this->assertInstanceOf(CollectionProxyInterface::class, $tomHanks->getLastestActedInMeta());

        $this->assertEquals(4, $tomHanks->getLastestActedIn()->count());
        // Now the relation should have been loaded and switched to an ArrayCollection
        $this->assertInstanceOf(ArrayCollection::class, $tomHanks->getLastestActedIn());

        foreach ($tomHanks->getLastestActedIn() as $movie) {
            $this->assertInstanceOf(NodeProxyInterface::class, $movie);
            $this->assertInstanceOf(Movie::class, $movie);
        }
        // Check the meta were loaded
        $this->assertEquals(4, $tomHanks->getLastestActedInMeta()->count());
        $this->assertInstanceOf(ArrayCollection::class, $tomHanks->getLastestActedInMeta());

        /** @var Movie */
        $cloudAtlas = $tomHanks->getLastestActedIn()->get(0);
        $this->assertEquals('Cloud Atlas', $cloudAtlas->getTitle());
        $this->assertEquals(54.25, $cloudAtlas->getAverageActorsAge());
        $this->assertEquals(95, $cloudAtlas->getRating());
        $this->assertEquals(2, $cloudAtlas->getOldestTwoActors()->count());
        $this->assertEquals('Jim Broadbent', $cloudAtlas->getOldestTwoActors()->get(0)->getName());
        $this->assertEquals('Tom Hanks', $cloudAtlas->getOldestTwoActors()->get(1)->getName());
        $movieMeta = $tomHanks->getLastestActedInMeta()->get(0);
        $this->assertEquals(4, count($movieMeta->getRoles()));
        $this->assertContains('Zachry', $movieMeta->getRoles());
        $this->assertContains('Dr. Henry Goose', $movieMeta->getRoles());
        $this->assertContains('Isaac Sachs', $movieMeta->getRoles());
        $this->assertContains('Dermot Hoggins', $movieMeta->getRoles());

        $this->assertEquals('Charlie Wilson\'s War', $tomHanks->getLastestActedIn()->get(1)->getTitle());
        $movieMeta = $tomHanks->getLastestActedInMeta()->get(1);
        $this->assertEquals(1, count($movieMeta->getRoles()));
        $this->assertContains('Rep. Charlie Wilson', $movieMeta->getRoles());

        $this->assertEquals('The Da Vinci Code', $tomHanks->getLastestActedIn()->get(2)->getTitle());
        $movieMeta = $tomHanks->getLastestActedInMeta()->get(2);
        $this->assertEquals(1, count($movieMeta->getRoles()));
        $this->assertContains('Dr. Robert Langdon', $movieMeta->getRoles());

        $this->assertEquals('The Polar Express', $tomHanks->getLastestActedIn()->get(3)->getTitle());
        $movieMeta = $tomHanks->getLastestActedInMeta()->get(3);
        $this->assertEquals(6, count($tomHanks->getLastestActedInMeta()->get(3)->getRoles()));
        $this->assertContains('Hero Boy', $movieMeta->getRoles());
        $this->assertContains('Scrooge', $movieMeta->getRoles());
        $this->assertContains('Father', $movieMeta->getRoles());
        $this->assertContains('Hobo', $movieMeta->getRoles());
        $this->assertContains('Conductor', $movieMeta->getRoles());
        $this->assertContains('Santa Claus', $movieMeta->getRoles());

        $this->assertInstanceOf(ArrayCollection::class, $tomHanks->getDirected());
        $this->assertEquals(1, $tomHanks->getDirected()->count());

        foreach ($tomHanks->getDirected() as $movie) {
            $this->assertInstanceOf(NodeProxyInterface::class, $movie);
            $this->assertInstanceOf(Movie::class, $movie);
        }
        $this->assertEquals('That Thing You Do', $tomHanks->getDirected()->first()->getTitle());
    }

    public function testMovie()
    {
        /** @var Movie */
        $matrix = $this->nm->getRepository(Movie::class)->findOneBy(['title' => 'The Matrix']);

        $this->assertEquals(5, count($matrix->getActors()));

        $this->assertEquals(1, $matrix->getFemaleActors()->count());
        $this->assertEquals('Carrie-Anne Moss', $matrix->getFemaleActors()->get(0)->getName());

        $this->assertEquals(4, $matrix->getMaleActors()->count());
        $this->assertEquals('Hugo Weaving', $matrix->getMaleActors()->get(0)->getName());
        $this->assertEquals('Laurence Fishburne', $matrix->getMaleActors()->get(1)->getName());
        $this->assertEquals('Keanu Reeves', $matrix->getMaleActors()->get(2)->getName());
        $this->assertEquals('Emil Eifrem', $matrix->getMaleActors()->get(3)->getName());
    }
}
