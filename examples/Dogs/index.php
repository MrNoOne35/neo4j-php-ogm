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
use Neo4j\OGM\Examples\Dogs\Entity\Dog;
use Neo4j\OGM\Examples\Dogs\Relationship\Breeded;
use Neo4j\OGM\Repository\RepositoryInterface;

$boltUrl = getenv('NEO4J_URL');

$nm = Common::getNodeManager($boltUrl, __DIR__.DIRECTORY_SEPARATOR.'var');

$nm->getClient()->run('MATCH (n:Dog) DETACH DELETE n');

$dogRespository = $nm->getRepository(Dog::class);
$breededRepository = $nm->getRepository(Breeded::class);

loadDogs($dogRespository, $breededRepository);

// Let's clear all internal caches, we want fresh data
$nm->clear();

/** @var Dog */
$lola = $dogRespository->findOneBy(['name' => 'Lola']);
$issues = $lola->getMotherMeta()->getIssues();
$issues[] = 'REJECTED';
$lola->getMotherMeta()->setIssues($issues);
$breededRepository->save($lola->getMotherMeta());

/** @var Dog */
$bella = $dogRespository->findOneBy(['name' => 'Bella']);
foreach ($bella->getCubs() as $index => $cub) {
    echo $bella->getName().' gave birth to '.$cub->getName().' ('.$cub->getGender().') the '.$cub->getBirthdate()->format('Y-m-d').', issues: '.join(', ', $bella->getCubsMeta()->get($index)->getIssues())."\n";
}

function loadDogs(
    RepositoryInterface $dogRespository,
    RepositoryInterface $breededRepository
): void {
    $bella = new Dog();
    $bella
        ->setName('Bella')
        ->setBirthdate((new \DateTime())->sub(new \DateInterval('P365D')))
        ->setGender(Gender::FEMALE)
    ;
    $dogRespository->save($bella);

    $buster = new Dog();
    $buster
        ->setName('Buster')
        ->setBirthdate((new \DateTime())->sub(new \DateInterval('P365D')))
        ->setGender(Gender::MALE)
    ;
    $dogRespository->save($buster);

    $bentley = addCub(
        $dogRespository,
        $breededRepository,
        $bella,
        $buster,
        'Bentley',
        Gender::MALE
    );

    addCub(
        $dogRespository,
        $breededRepository,
        $bella,
        $buster,
        'Riley',
        Gender::MALE
    );

    addCub(
        $dogRespository,
        $breededRepository,
        $bella,
        $buster,
        'Roxy',
        Gender::FEMALE
    );

    $lola = addCub(
        $dogRespository,
        $breededRepository,
        $bella,
        $buster,
        'Lola',
        Gender::FEMALE
    );

    addCub(
        $dogRespository,
        $breededRepository,
        $bella,
        $buster,
        'Ginger',
        Gender::FEMALE
    );

    // We want all the cubs to show up,
    // we need to refresh the mother and father
    $dogRespository->refresh($bella);
    $dogRespository->refresh($buster);

    echo '[Loading] The mother is '.$bella->getName().' and she has '.$bella->getCubs()->count()." cubs\n";
    echo '[Loading] The father is '.$buster->getName().' and he has '.$buster->getCubs()->count()." cubs\n";
    echo '[Loading] The mother of '.$lola->getName().' is '.$lola->getMother()->getName()."\n";
    echo '[Loading] The father of '.$bentley->getName().' is '.$bentley->getFather()->getName()."\n";
}

function addCub(
    RepositoryInterface $dogRespository,
    RepositoryInterface $breededRepository,
    Dog $mother,
    Dog $father,
    string $cubName,
    string $gender
): Dog {
    $cub = new Dog();
    $cub
        ->setName($cubName)
        ->setBirthdate(new \DateTime())
        ->setGender($gender)
    ;
    $dogRespository->save($cub);

    $breeded = new Breeded();
    $breeded
        ->setParent($mother)
        ->setCub($cub)
    ;
    $breededRepository->save($breeded);

    $breeded = new Breeded();
    $breeded
        ->setParent($father)
        ->setCub($cub)
    ;
    $breededRepository->save($breeded);

    // We want the mother and father to show up
    // we need to reload the cub
    $dogRespository->reload($cub);

    return $cub;
}
