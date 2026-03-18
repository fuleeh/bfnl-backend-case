<?php

declare(strict_types=1);

namespace App\Api\SportVenues\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Api\SportVenues\Service\SportVenueService;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class SportVenueCollectionStateProvider implements ProviderInterface
{
    public function __construct(
        private SportVenueService $service,
        private Pagination $pagination,
        private RequestStack $requestStack,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        [$page, $limit] = $this->pagination->getPagination($operation, $context);

        $limit = $limit > 0 ? $limit : 30;
        $offset = ($page - 1) * $limit;

        $request = $this->requestStack->getCurrentRequest();

        $filters = [];
        if ($request !== null) {
            $lat = $request->query->get('lat');
            $lng = $request->query->get('lng');
            if ($lat !== null && $lng !== null) {
                $filters = [
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'distance' => (float) $request->query->get('distance', 10.0),
                ];
            }
        }

        $result = $this->service->findWithPagination(['filters' => $filters], $limit, $offset);

        return $result['items'];
    }
}
