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
}
