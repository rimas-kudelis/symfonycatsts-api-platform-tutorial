<?php

namespace App\Tests\Functional;

use App\Factory\CheeseListingFactory;
use App\Factory\UserFactory;
use App\Test\ApiTestCase;

class CheeseListingResourceTest extends ApiTestCase
{
    public function testCreateCheeseListing(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cheeses');
        $this->assertResponseStatusCodeSame(401);

        $authenticatedUser = UserFactory::new()->create();
        $otherUser = UserFactory::new()->create();
        $this->logIn($client, $authenticatedUser);

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
        $user1 = UserFactory::new()->create();
        $user2 = UserFactory::new()->create();

        $cheeseListing = CheeseListingFactory::new()->published()->create([
            'owner' => $user1,
        ]);

        $this->logIn($client, $user2);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            // try to trick security by reassigning to this user
            'json' => ['title' => 'Updated', 'owner' => '/api/users/'.$user2->getId()],
        ]);
        $this->assertResponseStatusCodeSame(403, 'only author can update');

        $this->logIn($client, $user1);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => ['title' => 'Updated'],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetCheeseListingCollection()
    {
        $client = static::createClient();
        $user = UserFactory::new()->create();

        $factory = CheeseListingFactory::new(['owner' => $user]);
        // CL 1: unpublished
        $factory->create();

        // CL 2: published
        $cheeseListing2 = $factory->published()->create([
            'title' => 'cheese2',
            'description' => 'cheese',
            'price' => 1000,
        ]);

        // CL 3: published
        $factory->published()->create();

        $client->request('GET', '/api/cheeses');
        $this->assertJsonContains(['hydra:totalItems' => 2]);
        $this->assertJsonContains(['hydra:member' => [
            0 => [
                '@id' => '/api/cheeses/' . $cheeseListing2->getId(),
                '@type' => 'Cheese',
                'title' => 'cheese2',
                'description' => 'cheese',
                'price' => 1000,
                'owner' => '/api/users/' . $user->getId(),
                'shortDescription' => 'cheese',
                'createdAtAgo' => '1 second ago',
            ]
        ]]);
    }

    public function testGetCheeseListingItem()
    {
        $client = static::createClient();
        $user = UserFactory::new()->create();
        $this->logIn($client, $user);
        $otherUser = UserFactory::new()->create();

        $cheeseListing1 = CheeseListingFactory::new()->create(['owner' => $otherUser]);

        $client->request('GET', '/api/cheeses/'.$cheeseListing1->getId());
        $this->assertResponseStatusCodeSame(404);

        $response = $client->request('GET', '/api/users/'.$otherUser->getId());
        $data = $response->toArray();
        $this->assertEmpty($data['cheeseListings']);
    }
}
