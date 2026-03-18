<?php

declare(strict_types=1);

namespace App\Api\SportVenues\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Api\SportVenues\Dto\SportVenueDto;
use App\Api\SportVenues\State\Provider\SportVenueCollectionStateProvider;

#[ApiResource(
    shortName: 'SportVenue',
    description: 'API for retrieving sport venues with location-based filtering',
    normalizationContext: ['groups' => [SportVenueDto::GROUP_READ]],
    operations: [
        new GetCollection(
            provider: SportVenueCollectionStateProvider::class,
            output: SportVenueDto::class,
            paginationEnabled: true,
            normalizationContext: ['groups' => [SportVenueDto::GROUP_READ]],
            openapiContext: [
                'summary' => 'Retrieve sport venues',
                'description' => 'Retrieves a paginated collection of sport venues. Can be filtered by location using lat, lng, and distance parameters.',
                'tags' => [
                    ['name' => 'Sport Venues', 'description' => 'Get list of venues'],
                ],
                'parameters' => [
                    [
                        'name' => 'lat',
                        'in' => 'query',
                        'description' => 'Latitude coordinate for filtering venues by distance (-90 to 90)',
                        'schema' => ['type' => 'number', 'format' => 'float', 'minimum' => -90, 'maximum' => 90],
                        'example' => 48.8566,
                    ],
                    [
                        'name' => 'lng',
                        'in' => 'query',
                        'description' => 'Longitude coordinate for filtering venues by distance (-180 to 180)',
                        'schema' => ['type' => 'number', 'format' => 'float', 'minimum' => -180, 'maximum' => 180],
                        'example' => 2.3522,
                    ],
                    [
                        'name' => 'distance',
                        'in' => 'query',
                        'description' => 'Maximum distance in kilometers from the given coordinates',
                        'schema' => ['type' => 'number', 'format' => 'float', 'minimum' => 0, 'default' => 10],
                        'example' => 50,
                    ],
                ],
                'responses' => [
                    '200' => ['description' => 'Successful response'],
                    '401' => ['description' => 'Unauthorized - JWT token required'],
                    '400' => ['description' => 'Invalid query parameters'],
                ],
            ],
        ),
    ],
)]
class SportVenueApiResource
{
}
