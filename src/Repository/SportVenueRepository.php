<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SportVenue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SportVenueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SportVenue::class);
    }

    public function findWithPagination(
        array $options = [],
        int $limit = 30,
        int $offset = 0
    ): array {
        $filters = $options['filters'] ?? [];
        $lat = $filters['lat'] ?? null;
        $lng = $filters['lng'] ?? null;
        $distance = $filters['distance'] ?? 10.0;

        if ($lat !== null && $lng !== null) {
            return $this->findByLocationWithPagination($lat, $lng, $distance, $limit, $offset);
        }

        return $this->findDefaultPaginated($limit, $offset);
    }

    private function findDefaultPaginated(int $limit, int $offset): array
    {
        $total = $this->count([]);
        
        $qb = $this->createQueryBuilder('v');
        $qb->setFirstResult($offset)->setMaxResults($limit);
        $items = $qb->getQuery()->getResult();

        return ['total' => $total, 'items' => $items];
    }

    private function findByLocationWithPagination(
        float $lat,
        float $lng,
        float $distanceKm,
        int $limit,
        int $offset
    ): array {
        $connection = $this->getEntityManager()->getConnection();

        $countSql = '
            SELECT COUNT(*) as total
            FROM sport_venue
            WHERE ST_Distance_Sphere(
                POINT(CAST(lng AS DECIMAL(10,6)), CAST(lat AS DECIMAL(10,6))),
                POINT(:lng, :lat)
            ) / 1000 <= :distance
        ';

        $countStmt = $connection->prepare($countSql);
        $countStmt->bindValue('lat', $lat);
        $countStmt->bindValue('lng', $lng);
        $countStmt->bindValue('distance', $distanceKm);
        $total = (int) $countStmt->executeQuery()->fetchOne();

        if ($total === 0) {
            return ['total' => 0, 'items' => []];
        }

        $sql = '
            WITH venue_with_distance AS (
                SELECT id, name, lat, lng,
                    ST_Distance_Sphere(
                        POINT(CAST(lng AS DECIMAL(10,6)), CAST(lat AS DECIMAL(10,6))),
                        POINT(:lng, :lat)
                    ) / 1000 AS distance
                FROM sport_venue
            )
            SELECT id, name, lat, lng, distance
            FROM venue_with_distance
            WHERE distance <= :distance
            ORDER BY distance ASC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue('lat', $lat);
        $stmt->bindValue('lng', $lng);
        $stmt->bindValue('distance', $distanceKm);
        $stmt->bindValue('limit', $limit, \Doctrine\DBAL\ParameterType::INTEGER);
        $stmt->bindValue('offset', $offset, \Doctrine\DBAL\ParameterType::INTEGER);
        $rows = $stmt->executeQuery()->fetchAllAssociative();

        if (empty($rows)) {
            return ['total' => $total, 'items' => []];
        }

        $ids = array_column($rows, 'id');
        $items = $this->findBy(['id' => $ids]);
        usort($items, fn($a, $b) => array_search($a->getId(), $ids) <=> array_search($b->getId(), $ids));

        return ['total' => $total, 'items' => $items];
    }
}
