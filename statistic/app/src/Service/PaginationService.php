<?php


namespace App\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationService
{
    private ?Request $request;

    public function __construct(RequestStack $request_stack)
    {
        $this->request = $request_stack->getCurrentRequest();
    }

    public function paginate($query, int $limit): Paginator
    {
        $currentPage = $this->request->query->getInt('p') ?: 1;
        $paginator = new Paginator($query);
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($currentPage - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    public function lastPage(Paginator $paginator): int
    {
        return ceil($paginator->count() / $paginator->getQuery()
                ->getMaxResults());
    }

    public function total(Paginator $paginator): int
    {
        return $paginator->count();
    }

    /**
     * @throws Exception
     */
    public function currentPageHasNoResult(Paginator $paginator): bool
    {
        return !$paginator->getIterator()->count();
    }
}