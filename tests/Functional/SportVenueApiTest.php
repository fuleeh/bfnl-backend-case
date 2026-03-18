<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SportVenueApiTest extends ApiTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        $this->truncateTables();
        $this->createUserAndGetToken();
    }

    private function truncateTables(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $connection->executeStatement('TRUNCATE TABLE `user`');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function createUserAndGetToken(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword($this->getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'password'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->token = $this->getContainer()->get(JWTTokenManagerInterface::class)->create($user);
    }

    public function testGetCollectionWithoutAuthReturns401(): void
    {
        static::createClient()->request('GET', '/api/sport_venues');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetCollectionWithAuthReturns200(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/api/contexts/SportVenue',
            '@type' => 'hydra:Collection',
        ]);
    }

    public function testGetCollectionHasPagination(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHasHeader('content-type', 'application/ld+json; charset=utf-8');
    }

    public function testLoginWithValidCredentials(): void
    {
        $response = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'email' => 'test@example.com',
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $content = $response->toArray();
        $this->assertArrayHasKey('token', $content);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $response = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegisterNewUser(): void
    {
        $email = 'newuser' . uniqid() . '@example.com';

        $response = static::createClient()->request('POST', '/api/register', [
            'json' => [
                'email' => $email,
                'password' => 'newpassword',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['email' => $email]);
    }

    public function testRegisterDuplicateUser(): void
    {
        $response = static::createClient()->request('POST', '/api/register', [
            'json' => [
                'email' => 'test@example.com',
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testPaginationDefault(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testPaginationWithPageParameter(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues?page=1', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $content = $response->toArray();
        $this->assertArrayHasKey('hydra:totalItems', $content);
    }

    public function testPaginationWithItemsPerPage(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues?itemsPerPage=5', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $content = $response->toArray();
        $this->assertArrayHasKey('hydra:totalItems', $content);
    }

    public function testPaginationWithPageAndItemsPerPage(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues?page=2&itemsPerPage=10', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $content = $response->toArray();
        $this->assertArrayHasKey('hydra:totalItems', $content);
    }

    public function testLocationFilterWithAllParameters(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues?lat=48.8566&lng=2.3522&distance=50', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@type' => 'hydra:Collection',
        ]);
    }

    public function testLocationFilterWithDefaultDistance(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues?lat=48.8566&lng=2.3522', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testLocationFilterOnlyLat(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues?lat=48.8566', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testLocationFilterOnlyLng(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues?lng=2.3522', [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testInvalidTokenReturns401(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues', [
            'auth_bearer' => 'invalid_token_here',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMalformedTokenReturns401(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues', [
            'auth_bearer' => 'not.a.valid.jwt.token',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMissingAuthHeaderReturns401(): void
    {
        $response = static::createClient()->request('GET', '/api/sport_venues');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginWithNonExistentUser(): void
    {
        $response = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'email' => 'nonexistent@example.com',
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginWithMissingEmail(): void
    {
        $response = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLoginWithMissingPassword(): void
    {
        $response = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'email' => 'test@example.com',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterWithMissingEmail(): void
    {
        $response = static::createClient()->request('POST', '/api/register', [
            'json' => [
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterWithMissingPassword(): void
    {
        $response = static::createClient()->request('POST', '/api/register', [
            'json' => [
                'email' => 'test@example.com',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterWithInvalidEmail(): void
    {
        $response = static::createClient()->request('POST', '/api/register', [
            'json' => [
                'email' => 'not-an-email',
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterWithShortPassword(): void
    {
        $response = static::createClient()->request('POST', '/api/register', [
            'json' => [
                'email' => 'new@example.com',
                'password' => '123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager !== null) {
            $this->entityManager->close();
        }
    }
}
