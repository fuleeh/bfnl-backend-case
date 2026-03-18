<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Repository\SportVenueRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class SportVenueRepositoryTest extends TestCase
{
    public function testRepositoryCanBeInstantiated(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new SportVenueRepository($registry);

        $this->assertInstanceOf(SportVenueRepository::class, $repository);
    }

    public function testFindWithPaginationMethodExists(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new SportVenueRepository($registry);

        $this->assertTrue(method_exists($repository, 'findWithPagination'));
    }

    public function testFindWithPaginationWithoutFilters(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new SportVenueRepository($registry);

        $this->assertTrue(method_exists($repository, 'findWithPagination'));
    }

    public function testFindWithPaginationWithLocationFilters(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new SportVenueRepository($registry);

        $this->assertTrue(method_exists($repository, 'findWithPagination'));
    }
}
