<?php

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class CheeseListingResourceTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateCheeseListing(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', '/api/cheeses');

        $this->assertResponseStatusCodeSame(401);

        $user = new User();
        $user->setEmail('cheeseplease@example.com');
        $user->setUsername('cheeseplease');
        $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$Rg2+xerLwieLBVX3l0p+2A$d+biDK6NINKB60tkoPOLxJgTVXgKMc8DcC4vXvQGtbo');

        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $client->request('POST', '/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'cheeseplease@example.com',
                'password' => 'foo',
            ],
        ]);
        $this->assertResponseStatusCodeSame(204);
    }
}
