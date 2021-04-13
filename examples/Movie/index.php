<?php
/*
 * This file is part of the Neo4j PHP OGM package.
 *
 * (c) Frédéric Giudicelli https://github.com/giudicelli/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\OGM\Examples\Dogs;

include_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

use Neo4j\OGM\Examples\Common;
use Neo4j\OGM\Examples\Movie\Entity\Movie;

$boltUrl = getenv('NEO4J_URL');

$nm = Common::getNodeManager($boltUrl, __DIR__.DIRECTORY_SEPARATOR.'var');

$nm->getClient()->run('MATCH (n:Movie) DETACH DELETE n');
$nm->getClient()->run('MATCH (n:Person) DETACH DELETE n');

// Load all the data
$nm->getClient()->run(
    file_get_contents(
        __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'movie.txt'
    )
);

$movieRespository = $nm->getRepository(Movie::class);

/** @var Movie */
$matrix = $movieRespository->findOneBy(['title' => 'The Matrix']);

echo $matrix->getTitle().' ('.$matrix->getReleased().') has '.$matrix->getActors()->count()." actors\n";
foreach ($matrix->getFemaleActors() as $index => $actor) {
    echo "\t".$actor->getName().' ('.$actor->getBorn().') played '.join(', ', $matrix->getFemaleActorsMeta()->get($index)->getRoles())."\n";
}
foreach ($matrix->getMaleActors() as $index => $actor) {
    echo "\t".$actor->getName().' ('.$actor->getBorn().') played '.join(', ', $matrix->getFemaleActorsMeta()->get($index)->getRoles())."\n";
}
