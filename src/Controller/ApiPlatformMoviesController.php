<?php declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Entity\Movie;
use App\Service\PaginationLimiterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The "ApiPlatformMoviesController" class
 */
class ApiPlatformMoviesController extends AbstractController
{
    public function digest(
        int $weekNumber,
        ParameterBagInterface $parameterBag,
        Request $request,
        EntityManagerInterface $entityManager
    )
    {
        $apiPlatformCollectionPagination = $parameterBag->get('api_platform.collection.pagination');
        $page = (int)$request->get($apiPlatformCollectionPagination['page_parameter_name'], 1);
        $perPage = (int)$request->get(
            $apiPlatformCollectionPagination['items_per_page_parameter_name'],
            $apiPlatformCollectionPagination['items_per_page']
        );
        $maxPerPage = $apiPlatformCollectionPagination['maximum_items_per_page'];

        $paginationLimiter = new PaginationLimiterService($page, $perPage, $maxPerPage);
        $doctrinePaginator = $entityManager->getRepository(Movie::class)->digest($weekNumber, $paginationLimiter);

        return new Paginator($doctrinePaginator);
    }
}