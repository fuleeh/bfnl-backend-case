<?php

declare(strict_types=1);

namespace App\Api\SportVenues\Service;

use App\Api\SportVenues\Dto\DtoMapper\SportVenueDtoMapper;
use App\Repository\SportVenueRepository;

readonly class SportVenueService
{
    public function __construct(
        private SportVenueRepository $repository,
        private SportVenueDtoMapper $mapper,
    ) {}

    /**
     * @param array{filters?: array{lat?: float, lng?: float, distance?: float}} $options
     * @return array{total: int, items: array}
     */
    public function findWithPagination(array $options = [], int $limit = 30, int $offset = 0): array
    {
        $result = $this->repository->findWithPagination($options, $limit, $offset);

        return [
            'total' => $result['total'],
            'items' => $this->mapper->fromEntityCollection($result['items']),
        ];
    }
}
