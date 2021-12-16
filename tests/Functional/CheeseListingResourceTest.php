<?php

namespace App\Tests\Functional;

use App\Entity\CheeseListing;
use App\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class CheeseListingResourceTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateCheeseListing(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cheeses');
        $this->assertResponseStatusCodeSame(401);

        $authenticatedUser = $this->createUserAndLogIn($client, 'cheeseplease@example.com', 'foo');
        $otherUser = $this->createUser('cheeselord@example.com', 'foo');

        $cheeseData = [
            'title' => 'Some tasty cheese',
            'description' => 'Very very tasty',
            'price' => 5000,
        ];

        $client->request('POST', '/api/cheeses', [
            'json' => $cheeseData,
        ]);
        $this->assertResponseStatusCodeSame(201);

        $client->request('POST', '/api/cheeses', [
            'json' => $cheeseData + ['owner' => '/api/users/'.$otherUser->getId()],
        ]);
        $this->assertResponseStatusCodeSame(422, 'Wrong owner passed');

        $client->request('POST', '/api/cheeses', [
            'json' => $cheeseData + ['owner' => '/api/users/'.$authenticatedUser->getId()],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testUpdateCheeseListing(): void
    {
        $client = static::createClient();
        $user1 = $this->createUser('user1@example.com', 'foo');
        $user2 = $this->createUser('user2@example.com', 'foo');

        $cheeseListing = new CheeseListing('Block of Cheddar');
        $cheeseListing->setOwner($user1);
        $cheeseListing->setPrice(1000);
        $cheeseListing->setDescription('Some spicy cheese');

        $em = static::getEntityManager();
        $em->persist($cheeseListing);
        $em->flush();

        $this->logIn($client, 'user2@example.com', 'foo');
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => ['title' => 'Updated', 'owner' => '/api/users/'.$user2->getId()],
        ]);
        $this->assertResponseStatusCodeSame(403);

        $this->logIn($client, 'user1@example.com', 'foo');
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => ['title' => 'Updated'],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetCheeseListingCollection()
    {
        $client = static::createClient();
        $user = $this->createUser('cheesebaron@example.com', 'foo');

        $cheeseListing1 = new CheeseListing('cheese1');
        $cheeseListing1->setOwner($user);
        $cheeseListing1->setTitle('Nice cheese');
        $cheeseListing1->setPrice(1000);
        $cheeseListing1->setDescription('Smelly');

        $cheeseListing2 = new CheeseListing('cheese1');
        $cheeseListing2->setOwner($user);
        $cheeseListing2->setTitle('Nice cheese 2');
        $cheeseListing2->setPrice(1000);
        $cheeseListing2->setDescription('Smelly');
        $cheeseListing2->setIsPublished(true);

        $cheeseListing3 = new CheeseListing('cheese1');
        $cheeseListing3->setOwner($user);
        $cheeseListing3->setTitle('Nice cheese 3');
        $cheeseListing3->setPrice(1000);
        $cheeseListing3->setDescription('Smelly');
        $cheeseListing3->setIsPublished(true);

        $em = $this->getEntityManager();
        $em->persist($cheeseListing1);
        $em->persist($cheeseListing2);
        $em->persist($cheeseListing3);
        $em->flush();

        $client->request('GET', '/api/cheeses');
        $this->assertJsonContains(['hydra:totalItems' => 2]);
    }

    public function testGetCheeseListingItem()
    {
        $client = static::createClient();
        $user = $this->createUser('cheesebaron@example.com', 'foo');

        $cheeseListing1 = new CheeseListing('cheese1');
        $cheeseListing1->setOwner($user);
        $cheeseListing1->setTitle('Nice cheese');
        $cheeseListing1->setPrice(1000);
        $cheeseListing1->setDescription('Smelly');
        $cheeseListing1->setIsPublished(false);

        $em = $this->getEntityManager();
        $em->persist($cheeseListing1);
        $em->flush();

        $client->request('GET', '/api/cheeses/'.$cheeseListing1->getId());
        $this->assertResponseStatusCodeSame(404);
    }
}
