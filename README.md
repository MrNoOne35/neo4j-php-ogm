[![Tests](https://github.com/giudicelli/neo4j-php-ogm/actions/workflows/tests.yml/badge.svg)](https://github.com/giudicelli/neo4j-php-ogm/actions/workflows/tests.yml)

# Neo4j PHP OGM

This a complete redevelopment of a Neo4j PHP OGM. It was inspired by [GraphAware Neo4j PHP OGM](https://github.com/graphaware/neo4j-php-ogm), although it doesn't support nearly as many features as Graphaware's OGM does. Redeveloping a full Doctrine compatible OGM would be too much work. But as features are added it will come close to it.

A few features from Doctrine are being used, such as parsing and caching annotations.

It uses [Laudis Neo4j PHP Client](https://github.com/neo4j-php/neo4j-php-client) which is the only PHP client [recommended by Neo4j](https://neo4j.com/developer/php/).

This bundle supports lazy loading for entities and collections.

# Table of Contents
1. [Installation](#Installation)
2. [Example](#Example)
3. [Documentation](#Documentation)
    1. [Model](#Model)
    2. [Annotations](#Annotations)
        1. [@OGM/Entity](#OgmEntity)
        2. [@OGM/Relationship](#OgmRelationship)
        3. [@OGM/Id](#OgmId)
        4. [@OGM/StartEntity](#OgmStartEntity)
        5. [@OGM/EndEntity](#OgmEndEntity)
        6. [@OGM/Property](#OgmProperty)
        7. [@OGM/Convert](#OgmConvert)
        8. [@OGM/QueryResult](#OgmQueryResult)
        9. [@OGM/Relation](#OgmRelation)
    3. [Repository](#Repository)
    4. [Events](#Events)
        1. [NodeCreatedEvent](#NodeCreatedEvent)
        2. [NodeUpdatedEvent](#NodeUpdatedEvent)
        3. [NodeDeletedEvent](#NodeDeletedEvent)
        4. [NodeLoadedEvent](#NodeLoadedEvent)
4. [FAQ](#FAQ)
    1. [How do I query a node by its Neo4j id?](#how-do-i-query-a-node-by-its-neo4j-id)
    2. [Can I filter nodes by their relations?](#can-i-filter-nodes-by-their-relations)
    3. [Can I run custom queries?](#can-i-run-custom-queries)
    4. [Can I run custom queries and get hydrated objects ?](#can-i-run-custom-queries-and-get-hydrated-objects)
5. [License](#License)


# Installation

Install with composer

```cli
composer require giudicelli/neo4j-php-ogm
```
# Example

For more complete examples, please check the *examples* directory.

```php
<?php
namespace App;

use Neo4j\OGM\NodeManagerInterface;
use App\Entity\Movie;
use App\Entity\Person;
use App\Relationship\ActedIn;

function loadTomHanks(NodeManagerInterface $nm): Person {
    $tomHanks = new Person();
    $tomHanks
        ->setName('Tom Hanks')
        ->setBorn(1956)
        ->setGender('MALE')
    ;
    $nm->getRepository(Person::class)->save($tomHanks);

    $forrestGump = new Movie();
    $forrestGump
        ->setTitle('Forrest Gump')
        ->setReleased(1994)
        ->setTagline('The world will never be the same once you\'ve seen it through the eyes of Forrest Gump.')
    ;
    $nm->getRepository(Movie::class)->save($forrestGump);

    // This is a relationship
    $actedIn = new ActedIn();
    $actedIn
        ->setPerson($tomHanks)
        ->setMovie($forrestGump)
        ->setRoles(['Forrest Gump'])
    ;
    $nm->getRepository(ActedIn::class)->save($forrestGump);

    // Now we need to refresh both entities
    // for the newly linked entities to show up
    $nm->getRepository(Person::class)->refresh($tomHanks);
    $nm->getRepository(Movie::class)->refresh($forrestGump);

    return $tomHanks;
}

```
# Documentation
## Model

An entity node must implement the **\Neo4j\OGM\Model\EntityInterface** interface.

A relationship node must implement the **\Neo4j\OGM\Model\RelationshipInterface** interface.

If you want the OGM to automatically handle the *created* and *updated* time for the nodes, you need to implement:
- **\Neo4j\OGM\Model\NodeCreatedAtInterface** interface, we also provide a trait **\Neo4j\OGM\Model\NodeCreatedAtTrait** you may use for a simple implementation.
- **\Neo4j\OGM\Model\NodeUpdatedAtInterface** interface, we also provide a trait **\Neo4j\OGM\Model\NodeUpdatedAtTrait** you may use for a simple implementation.

## Annotations

### @OGM/Entity

Allows to declare an entity.

|Option | Description | Required | Value |
| --- | --- | --- | --- |
|*label* | The Neo4j node label | yes | The label |
|*repository* | Specify a custom repository to handle this entity | no | The class name of the custom repository |


```php
<?php
namespace App\Entity;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Movie")
 */
class Movie implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```

### @OGM/Relationship

Allows to declare a relationship.

|Option | Description | Required | Value |
| --- | --- | --- | --- |
|*type* | The Neo4j relationship type | yes |  |
|*unique* | Is this relationship unique between the two entities | no | true/false (default true) |
|*repository* | Specify a custom repository to handle this entity | no | The class name of the custom repository |

```php
<?php
namespace App\Entity;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\RelationshipInterface;

/**
 * @OGM\Relationship(type="ACTED_IN")
 */
class ActedIn implements RelationshipInterface
{
    /**
     * @OGM\Id()
     */
    private ?int  $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```

### @OGM/Id

The propery that holds the id generated by Neo4j. Both **@OGM\Entity** and **@OGM\Relationship** require the presence of this annotation.

```php
<?php
namespace App\Entity;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Movie")
 */
class Movie implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```

### @OGM/StartEntity

Allows to declare the start node of a relationship. **@OGM/Relationship** requires the presence of this annotation.

|Option | Description | Required |
| --- | --- | --- |
|*target* | The entity class | yes |

```php
<?php
namespace App\Entity;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\RelationshipInterface;
use App\Entity\Person;

/**
 * @OGM\Relationship(type="ACTED_IN")
 */
class ActedIn implements RelationshipInterface
{
    /**
     * @OGM\Id()
     */
    private ?int  $id = null;

    /**
     * @OGM\StartEntity(target=Person::class)
     */
    private ?Person $person = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }
}
```

### @OGM/EndEntity

Allows to declare the end node of a relationship. **@OGM/Relationship** requires the presence of this annotation.

|Option | Description | Required |
| --- | --- | --- |
|*target* | The entity class | yes |

```php
<?php
namespace App\Entity;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\RelationshipInterface;
use App\Entity\Movie;
use App\Entity\Person;

/**
 * @OGM\Relationship(type="ACTED_IN")
 */
class ActedIn implements RelationshipInterface
{
    /**
     * @OGM\Id()
     */
    private ?int  $id = null;

    /**
     * @OGM\StartEntity(target=Person::class)
     */
    private ?Person $person = null;

    /**
     * @OGM\EndEntity(target=Movie::class)
     */
    private ?Movie $movie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): self
    {
        $this->movie = $movie;

        return $this;
    }
}
```

### @OGM/Property

Allows to declare a property to store in Neo4j. It applies to both **@OGM/Entity** and **@OGM/Relationship**.

|Option | Description | Required | Value |
| --- | --- | --- | --- |
|*type* | Enforce the type | no | One of *"string"*, *"boolean"*, *"array"*, *"int"*, *"double"* |
|*nullable* | Can this field be null | no | true/false (default is true) |
|*key* | Use this key as the property name on Neo4j | no |  |

```php
<?php
namespace App\Entity;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Movie")
 */
class Movie implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\Property(type="string", nullable=false)
     */
    private $title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
```

### @OGM/Convert

Convert an **@OGM/Property** before storing it in Neo4j or when retrieving it from Neo4j. It applies to both **@OGM/Entity** and **@OGM/Relationship**.

|Option | Description | Required | Value |
| --- | --- | --- | --- |
|*type* | The type of conversion | yes | *"datetime"* The value will be converted to and from a PHP's DateTime. *"datetime_immutable"* The value will be converted to and from a PHP's DateTimeImmutable. *"json"* The value will be converted to and from JSON. |
|*options* | The options to pass to the converter | no | See below |


**Options for "datetime" or "datetime_immutable"**

|Option | Description | Required | Value |
| --- | --- | --- | --- |
|*format* | The storing formate | no | *"timestamp"* to store as a unix timestamp, *"long_timestamp"* to store as unix timestamp but to keep the milliseconds precision, or any valid [format](https://www.php.net/manual/en/datetime.createfromformat.php) |
|*timezone* | The timezone to apply when loading the value | no | A valid PHP timezone |

```php
<?php
namespace App\Entity;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Movie")
 */
class Movie implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\Property(type="string", nullable=false)
     */
    private $title;

    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime", options={"format":"long_timestamp"})
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
```

### @OGM/QueryResult

Run a sub-query and return its result. It only applies to **@OGM/Entity**.

|Option | Description | Required | Value |
| --- | --- | --- | --- |
|*query* | The sub query to run | yes | The query must contain an *"{ENTRY}"* and an *"{OUTPUT}"*, these two values will be automatically populated at run time |
|*collection* | The returned value is a collection | no | true/false (default false) |
|*orderBy* | How to order the results | no | An array of fields with the sorting order (ASC/DESC) (See below for details) |
|*limit* | Limit the results to this number of items | no | A number (will be forced to 1 when collection is false) |

**Explanation on "orderBy"**

By default *orderBy* is applied to *{OUTPUT}*:

@OGM\QueryResult(query="MATCH ({ENTRY})<-[r:ACTED_IN]-(actor:Person) RETURN actor AS {OUTPUT}", collection=true, orderBy={"born"="ASC"})

*born* is a property of the *Person* entity identified by *actor* which is returned as *{OUTPUT}*.

If you want it to be applied to an intermediate value, you simply need to specify it:

@OGM\QueryResult(query="MATCH ({ENTRY})<-[r:ACTED_IN]-(actor:Person) RETURN actor.name AS {OUTPUT}", collection=true, orderBy={"r.onSetDate"="ASC"})

*onSetDate* is a property of the *ACTED_IN* relationship.

```php
<?php
namespace App\Entity;

use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Movie")
 */
class Movie implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\Property(type="string", nullable=false)
     */
    private $title;

    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime", options={"format"="long_timestamp"})
     */
    private $createdAt;

    /**
     * @OGM\QueryResult(query="MATCH ({ENTRY})<-[:ACTED_IN]-(actor:Person) RETURN actor.name AS {OUTPUT}",collection=true,limit=2,orderBy={"actor.born"="ASC"})
     */
    private $oldestTwoActors;

    /**
     * @OGM\QueryResult(query="MATCH ({ENTRY})<-[:ACTED_IN]-(actor:Person) RETURN avg({ENTRY}.released - actor.born) AS {OUTPUT}")
     */
    private $averageActorsAge;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOldestTwoActors(): array {
        return $this->oldestTwoActors;
    }

    public function getAverageActorsAge(): float {
        return $this->averageActorsAge;
    }
}
```

### @OGM/Relation

Get the associated other entities in a relationship with the entity. It only applies to **@OGM/Entity**.

|Option | Description | Required | Value |
| --- | --- | --- | --- |
|*relationship* | The relationship | yes | The class name of the relationship |
|*direction* | The direction of the relationship concerning the current entity | yes | *INCOMING* when the relationship's **@OGM/EndEntity** is this entity. *OUTGOING* when the relationship's **@OGM/StartEntity** is this entity. *BOTH* when the relationship goes both ways. |
|*collection* | The returned value is a collection | no | true/false (default false) |
|*orderBy* | How to order the results | no | An array of fields with the sorting order (ASC/DESC) (See below for details) |
|*limit* | Limit the results to this number of items | no | A number (will be forced to 1 when collection is false) |
|*fetch* | How the relations should be fetched | no | *EAGER* to force the loading. *LAZY* to lazy load (default when *collection* is false). *EXTRA_LAZY* to lazy load the whole collection (default when *collection* is true) (See below for details) |
|*filters* | Apply basic filters | no | An array of fields with their expected value (See below for details)  |

**Explanation on "fetch"**

1. *EAGER* means that all data is queried and loaded. This is very resources consuming, so unless you're absolutely sure you will need the data, avoid using it.

2. *LAZY* means only the ids are fetched. The actual real content is loaded the first time you try to access a property. When *collection* is true, the whole collection is built by the query but only the relations'id are returned. If your collection contains thousands of relations you do not want to use this as it would be very resources consuming.

3. *EXTRA_LAZY* only applies when *collection* is true. The collection is not built by the query, it only gets loaded the first time you try accessing it. When the collection is fetched, the relations it contains are *LAZY*.

**Explanation on "orderBy"**

By default *orderBy* is applied to the other end of the relationship:

@OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,limit=2,orderBy={"born"="ASC"})

*born* is a property of the other end of the *ActedIn* relationship.

If you want it to be applied to a property of the *ActedIn* relationship:

@OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,limit=2,orderBy={"{RELATIONSHIP}.onSetDate"="ASC"})

*onSetDate* is a property of the *ActedIn* relationship.

**Explanation on "filters"**

Applies basic filters on either the relationsip or on the other end of the relationship.

By default, the filters are applied the other end of the relationship:

@OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,limit=2,filters={"gender"="FEMALE"})

*gender* is a property of the other end of the *ActedIn* relationship.

If you want a filter to be applied to a property of the *ActedIn* relationship:

@OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,limit=2,filters={"{RELATIONSHIP}.Lorem"="IPSUM"})

*Lorem* is a property of the *ActedIn* relationship.

```php
<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neo4j\OGM\Annotation as OGM;
use Neo4j\OGM\Common\Direction;
use App\Relationship\ActedIn;
use App\Relationship\Directed;
use App\Relationship\Produced;
use App\Relationship\Reviewed;
use Neo4j\OGM\Model\EntityInterface;

/**
 * @OGM\Entity(label="Movie")
 */
class Movie implements EntityInterface
{
    /**
     * @OGM\Id()
     */
    private $id;

    /**
     * @OGM\Property(type="string")
     */
    private $title;

    /**
     * @OGM\Property(type="int")
     */
    private $released;

    /**
     * @OGM\Property(type="string")
     */
    private $tagline;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,orderBy={"born"="ASC"},relationshipProperty="actorsMeta")
     */
    private $actors;
    private $actorsMeta;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,orderBy={"born"="ASC"},relationshipProperty="femaleActorsMeta",filters={"gender"="FEMALE"})
     */
    private $femaleActors;
    private $femaleActorsMeta;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,orderBy={"born"="ASC"},relationshipProperty="femaleActorsMeta",filters={"gender"="MALE"})
     */
    private $maleActors;
    private $maleActorsMeta;

    /**
     * @OGM\Relation(relationship=Directed::class,direction=Direction::INCOMING,collection=true)
     */
    private $directors;

    /**
     * @OGM\Relation(relationship=Produced::class,direction=Direction::INCOMING,collection=true)
     */
    private $producers;

    /**
     * @OGM\Relation(relationship=Reviewed::class,direction=Direction::INCOMING,collection=true,relationshipProperty="reviewsMeta")
     */
    private $reviews;
    private $reviewsMeta;

    /**
     * @OGM\QueryResult(query="MATCH ({ENTRY})<-[r:REVIEWED]-(:Person) RETURN avg(r.rating) AS {OUTPUT}")
     */
    private $rating;

    /**
     * @OGM\Relation(relationship=ActedIn::class,direction=Direction::INCOMING,collection=true,limit=2,orderBy={"born"="ASC"})
     */
    private $oldestTwoActors;

    /**
     * @OGM\QueryResult(query="MATCH ({ENTRY})<-[:ACTED_IN]-(actor:Person) RETURN avg({ENTRY}.released - actor.born) AS {OUTPUT}")
     */
    private $averageActorsAge;

    public function __construct()
    {
        $this->actors = new ArrayCollection();
        $this->actorsMeta = new ArrayCollection();
        $this->directors = new ArrayCollection();
        $this->producers = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->reviewsMeta = new ArrayCollection();
        $this->oldestTwoActors = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getReleased(): int
    {
        return $this->released;
    }

    public function setReleased(int $released): self
    {
        $this->released = $released;

        return $this;
    }

    public function getTagline(): string
    {
        return $this->tagline;
    }

    public function setTagline(string $tagline): self
    {
        $this->tagline = $tagline;

        return $this;
    }

    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function getActorsMeta(): Collection
    {
        return $this->actorsMeta;
    }

    public function getFemaleActors(): Collection
    {
        return $this->femaleActors;
    }

    public function getFemaleActorsMeta(): Collection
    {
        return $this->femaleActorsMeta;
    }

    public function getMaleActors(): Collection
    {
        return $this->maleActors;
    }

    public function getMaleActorsMeta(): Collection
    {
        return $this->maleActorsMeta;
    }

    public function getDirectors(): Collection
    {
        return $this->directors;
    }

    public function getProducers(): Collection
    {
        return $this->producers;
    }

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function getReviewsMeta(): Collection
    {
        return $this->reviewsMeta;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function getOldestTwoActors(): Collection
    {
        return $this->oldestTwoActors;
    }

    public function getAverageActorsAge(): float
    {
        return $this->averageActorsAge;
    }
}
```
## Repository

The default node repository is **Neo4j\OGM\Repository\BaseRepository**. If you create a custom repository, you either need to extend **Neo4j\OGM\Repository\BaseRepository** or to implement **Neo4j\OGM\Repository\RepositoryInterface**.

## Events

### NodeCreatedEvent
The **\Neo4j\OGM\Event\NodeCreatedEvent** event is dispatched after a node (**@OGM\Entity** or **@OGM\Relationship**) has been created.

### NodeUpdatedEvent
The **\Neo4j\OGM\Event\NodeUpdatedEvent** event is dispatched after a node (**@OGM\Entity** or **@OGM\Relationship**) has been updated.

### NodeDeletedEvent
The **\Neo4j\OGM\Event\NodeDeletedEvent** event is dispatched after a node (**@OGM\Entity** or **@OGM\Relationship**) has been deleted.

### NodeLoadedEvent
The **\Neo4j\OGM\Event\NodeLoadedEvent** event is dispatched after a node (**@OGM\Entity** or **@OGM\Relationship**) has been fully loaded, not its eventual lazy instance.

# FAQ

## How do I query a node by its Neo4j id?

We've introduced a special operator *id()* which allows you to query a node by its id.
```php
<?php

$node = $repository->findOneBy(['id()' => $id]);

```
## Can I filter nodes by their relations?

In Doctrine you can easily filter results by their relation, which ends up creating a join for you.
Using OGM it's not possible at the time. But we're planning on adding this feature.

## Can I run custom queries?

Yes you can. The Neo4j client can be accessed through the *NodeManagerInterface::getClient* method.

## Can I run custom queries and get hydrated objects?

Yes you can. You need to use **Neo4j\OGM\Repository\BaseRepository**'s **findByQuery** or **findOneByQuery** method.

```php
<?php
namespace App;

use Neo4j\OGM\NodeManagerInterface;
use App\Entity\Movie;
use App\Entity\Person;
use App\Relationship\ActedIn;

/** @return Person[]|null */
function loadCostars(NodeManagerInterface $nm, string $name): ?array {
    $query = 'MATCH (p:Person)-[:ACTED_IN]->(:Movie)<-[:ACTED_IN]-(costar:Person)';
    $query .= PHP_EOL.'WHERE p.name = $name';
    $query .= PHP_EOL.'WITH DISTINCT costar AS costar';
    $params = ['name' => $name];

    return $nm->getRepository(Person::class)->findByQuery(
        'costar',
        $query,
        $params
    );
}

```

# License

The library is released under the MIT License, refer to the LICENSE file bundled with this package.

#Windows test in Powershell

$env:NEO4J_URL = 'bolt://neo4j:neo4j@localhost:7687'; php8 .\vendor\bin\phpunit
