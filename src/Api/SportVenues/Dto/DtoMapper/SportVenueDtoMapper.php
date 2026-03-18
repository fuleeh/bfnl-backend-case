<?php

declare(strict_types=1);

namespace App\Api\SportVenues\Dto\DtoMapper;

use App\Api\SportVenues\Dto\SportVenueDto;
use App\Entity\SportVenue;

readonly class SportVenueDtoMapper
{
    public function fromEntity(SportVenue $entity): SportVenueDto
    {
        return new SportVenueDto(
            id: $entity->getId(),
            name: $entity->getName(),
            lat: (float) $entity->getLat(),
            lng: (float) $entity->getLng(),
        );
    }

    /**
     * @param iterable<SportVenue> $entities
     * @return array<SportVenueDto>
     */
    public function fromEntityCollection(iterable $entities): array
    {
        $dtos = [];

        foreach ($entities as $entity) {
            $dtos[] = $this->fromEntity($entity);
        }

        return $dtos;
    }
}
