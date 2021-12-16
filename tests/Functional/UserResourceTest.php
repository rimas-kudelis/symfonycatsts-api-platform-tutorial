<?php

namespace App\Tests\Functional;


use App\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserResourceTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

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

        $this->logIn($client, 'cheeseplease@example.com', 'foo');
    }

    public function testUpdateUser(): void
    {
        $client = self::createClient();

        $user = $this->createUserAndLogIn($client, 'cheeseplease@example.com', 'foo');
        $client->request('PUT', '/api/users/'.$user->getId(), [
            'json' => ['username' => 'newUsername'],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newUsername',
        ]);
    }
}
