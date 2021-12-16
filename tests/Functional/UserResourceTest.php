<?php

namespace App\Tests\Functional;


use App\Entity\User;
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
            'json' => [
                'username' => 'newUsername',
                'roles' => ['ROLE_ADMIN'], // must be ignored
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newUsername',
        ]);

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->find($user->getId());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testGetUser(): void
    {
        $client = self::createClient();

        $user = $this->createUserAndLogIn($client, 'cheeseplease@example.com', 'foo');

        $user->setPhoneNumber('555.123.4567');
        $em = $this->getEntityManager();
        $em->flush();

        $client->request('GET', '/api/users/'.$user->getId());
        $this->assertJsonContains([
            'username' => 'cheeseplease',
        ]);

        $data = $client->getResponse()->toArray();
        $this->assertArrayNotHasKey('phoneNumber', $data);

        // refresh the user and elevate them to admin
        $user = $em->getRepository(User::class)->find($user->getId());
        $user->setRoles(['ROLE_ADMIN']);
        $em->flush();
        $this->logIn($client, 'cheeseplease@example.com', 'foo');

        $client->request('GET', '/api/users/'.$user->getId());
        $this->assertJsonContains([
            'username' => 'cheeseplease',
            'phoneNumber' => '555.123.4567',
        ]);
    }
}
