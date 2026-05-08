<?php
namespace LARAVEL\DatabaseCore\Traits\Eloquent;
use LARAVEL\DatabaseCore\Paginator;
use LARAVEL\DatabaseCore\LengthAwarePaginator;
trait Pagination
{
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $total = func_num_args() === 5 ? value(func_get_arg(4)) : $this->toBase()->getCountForPagination();
        $perPage = $perPage ?: $this->model->getPerPage();
        $page = $page ?: Paginator::resolveCurrentPage();
        $results = $total
            ? $this->forPage($page, $perPage)->get($columns)
            : $this->model->newCollection();

        return $this->makeRequest(
            $results,
            $total,
            $perPage,
            $page,
            ['path'=>getCurrentPath(),'pageName'=>$pageName]
        );
    }
    public function getSkip(int $currentPage, int $perPage): int
    {
        return (int) $currentPage == 1 ? 0 : ($currentPage - 1) * $perPage;
    }
    private function makeRequest($items,int $total, int $perPage, int $page, $options,$columns = ['*'])
    {
        return new LengthAwarePaginator($items,$total,$perPage,$page,$options);
    }
    // private function getPageUrl(): string
    // {
    //     $queryParams = request()->query();
    //     unset($queryParams['page']);
    //     $queryParams = http_build_query($queryParams);
    //     $appUrl = APP_URL;
    //     $uri = explode('?', $_SERVER['REQUEST_URI']);
    //     $pageUrl = $appUrl . array_shift($uri) . '?' . $queryParams;
    //     dd($pageUrl);
    //     return $pageUrl;
    // }
}
