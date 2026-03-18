<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\Api\SportVenues\Dto\SportVenueDTO;
use App\Api\SportVenues\Dto\DtoMapper\SportVenueDtoMapper;
use App\Entity\SportVenue;
use PHPUnit\Framework\TestCase;

class SportVenueDtoMapperTest extends TestCase
{
    private SportVenueDtoMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SportVenueDtoMapper();
    }

    public function testFromEntityCreatesDtoCorrectly(): void
    {
        $entity = new SportVenue();
        $entity->setName('Test Stadium');
        $entity->setLat('48.8566');
        $entity->setLng('2.3522');

        $dto = $this->mapper->fromEntity($entity);

        $this->assertInstanceOf(SportVenueDTO::class, $dto);
        $this->assertEquals('Test Stadium', $dto->name);
        $this->assertEquals(48.8566, $dto->lat);
        $this->assertEquals(2.3522, $dto->lng);
    }

    public function testDtoHasCorrectTypes(): void
    {
        $entity = new SportVenue();
        $entity->setName('Arena');
        $entity->setLat('51.5074');
        $entity->setLng('-0.1278');

        $dto = $this->mapper->fromEntity($entity);

        $this->assertNull($dto->id);
        $this->assertIsString($dto->name);
        $this->assertIsFloat($dto->lat);
        $this->assertIsFloat($dto->lng);
    }

    public function testFromEntityCollection(): void
    {
        $entity1 = new SportVenue();
        $entity1->setName('Stadium 1');
        $entity1->setLat('48.8566');
        $entity1->setLng('2.3522');

        $entity2 = new SportVenue();
        $entity2->setName('Stadium 2');
        $entity2->setLat('51.5074');
        $entity2->setLng('-0.1278');

        $entities = [$entity1, $entity2];
        $result = $this->mapper->fromEntityCollection($entities);

        $this->assertCount(2, $result);
        $this->assertEquals('Stadium 1', $result[0]->name);
        $this->assertEquals('Stadium 2', $result[1]->name);
    }

    public function testMapperIsReusable(): void
    {
        $entity = new SportVenue();
        $entity->setName('Test');
        $entity->setLat('1.0');
        $entity->setLng('1.0');

        $dto1 = $this->mapper->fromEntity($entity);
        $dto2 = $this->mapper->fromEntity($entity);

        $this->assertEquals($dto1->name, $dto2->name);
    }
}
