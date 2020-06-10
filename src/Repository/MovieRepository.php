<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Service\PaginationLimiterService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function findByTitle(array $criteria)
    {
        $title = $criteria['title'];

        return $this->createQueryBuilder('m')
            ->andWhere('LOWER(m.title) = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getResult();
    }

    public function digest(int $weekNumber, PaginationLimiterService $paginationLimiterService)
    {
        $year = date('Y');
        $weekNumberAndYear = sprintf('%02d-%d', $weekNumber, $year);
        $query = $this->createQueryBuilder('m')
                      ->andWhere("STRFTIME('%W-%Y', m.releasedAt) = :weekNumberAndYear")
                      ->setParameter('weekNumberAndYear', $weekNumberAndYear)
                      ->orderBy("STRFTIME('%W-%Y', m.releasedAt)")
                      ->setFirstResult($paginationLimiterService->offset())
                      ->setMaxResults($paginationLimiterService->limit())
                      ->getQuery();

        return new Paginator($query);
    }
}
