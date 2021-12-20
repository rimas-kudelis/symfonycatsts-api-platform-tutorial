<?php

namespace App\Tests\Functional;

use App\Factory\UserFactory;
use App\Test\ApiTestCase;
use Symfony\Component\Uid\Uuid;

class UserResourceTest extends ApiTestCase
{
    public function testCreateUser(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/users', [
            'json' => [
                'email' => 'cheeseplease@example.com',
                'username' => 'cheeseplease',
                'password' => 'foo',
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);

        $user = UserFactory::repository()->findOneBy(['email' => 'cheeseplease@example.com']);
        $this->assertNotNull($user);
        $this->assertJsonContains([
            '@id' => '/api/users/'.$user->getUuid(),
        ]);

        $this->logIn($client, 'cheeseplease@example.com', 'foo');
    }

    public function testCreateUserWithUuid(): void
    {
        $client = self::createClient();

        $uuid = Uuid::v4();
        $client->request('POST', '/api/users', [
            'json' => [
                'uuid' => $uuid,
                'email' => 'cheeseplease@example.com',
                'username' => 'cheeseplease',
                'password' => 'foo',
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);

        $this->assertJsonContains([
            '@id' => '/api/users/'.$uuid,
        ]);
    }

    public function testUpdateUser(): void
    {
        $client = self::createClient();
        $user = UserFactory::new()->create();
        $this->logIn($client, $user);

        $client->request('PUT', '/api/users/'.$user->getUuid(), [
            'json' => [
                'username' => 'newUsername',
                'roles' => ['ROLE_ADMIN'], // must be ignored
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newUsername'
        ]);

        $user->refresh();
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testGetUser(): void
    {
        $client = self::createClient();
        $user = UserFactory::new()->create([
            'phoneNumber' => '555.123.4567',
            'username' => 'cheesehead'
        ]);
        $authenticatedUser = UserFactory::new()->create();
        $this->logIn($client, $authenticatedUser);

        $client->request('GET', '/api/users/'.$user->getUuid());
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'username' => $user->getUsername(),
            'isMvp' => true,
        ]);

        $data = $client->getResponse()->toArray();
        $this->assertArrayNotHasKey('phoneNumber', $data);
        $this->assertJsonContains([
            'isMe' => false,
        ]);

        // refresh the user & elevate
        $user->refresh();
        $user->setRoles(['ROLE_ADMIN']);
        $user->save();
        $this->logIn($client, $user);

        $client->request('GET', '/api/users/'.$user->getUuid());
        $this->assertJsonContains([
            'phoneNumber' => '555.123.4567',
            'isMe' => true,
        ]);
    }
}
