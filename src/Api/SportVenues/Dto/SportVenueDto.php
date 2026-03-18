<?php

declare(strict_types=1);

namespace App\Api\SportVenues\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

readonly class SportVenueDto
{
    public const string GROUP_READ = 'sport_venue:read';

    public function __construct(
        #[ApiProperty(description: 'The unique identifier of the sport venue')]
        #[Groups([self::GROUP_READ])]
        public ?int $id,

        #[ApiProperty(description: 'The name of the sport venue', example: 'Stade de France')]
        #[Groups([self::GROUP_READ])]
        public string $name,

        #[ApiProperty(description: 'Latitude coordinate (-90 to 90)', example: '48.8566')]
        #[Groups([self::GROUP_READ])]
        public float $lat,

        #[ApiProperty(description: 'Longitude coordinate (-180 to 180)', example: '2.3522')]
        #[Groups([self::GROUP_READ])]
        public float $lng,
    ) {}
}
