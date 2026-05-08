<?php
namespace LARAVEL\DatabaseCore;
use ArrayAccess;
use Countable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use LARAVEL\DatabaseCore\AbstractPaginator;
use IteratorAggregate;
use JsonSerializable;
class LengthAwarePaginator extends AbstractPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, Jsonable, JsonSerializable, LengthAwarePaginatorContract
{
    protected $total;
    protected $lastPage;
    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        $this->options = $options;
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
        $this->total = $total;
        $this->perPage = (int) $perPage;
        $this->lastPage = max((int) ceil($total / $perPage), 1);
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;
        $this->currentPage = $this->setCurrentPage($currentPage, $this->pageName);
        $this->items = $items instanceof Collection ? $items : Collection::make($items);
    }
    protected function setCurrentPage($currentPage, $pageName)
    {
        $currentPage = $currentPage ?: static::resolveCurrentPage($pageName);
        return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
    }
    public function links($view = null, $data = [])
    {
        return $this->render($view, $data);
    }
    public function render($view = null, $data = [])
    {
        return \LARAVEL\Core\Support\Facades\View::view($view ?: static::$defaultView,array_merge($data,['paginator' => $this,'elements' => $this->elements()]));
    }
    public function linkCollection()
    {
        return collect($this->elements())->flatMap(function ($item) {
            if (! is_array($item)) {
                return [['url' => null, 'label' => '...', 'active' => false]];
            }

            return collect($item)->map(function ($url, $page) {
                return [
                    'url' => $url,
                    'label' => (string) $page,
                    'active' => $this->currentPage() === $page,
                ];
            });
        })->prepend([
            'url' => $this->previousPageUrl(),
            'label' => function_exists('__') ? __('pagination.previous') : 'Previous',
            'active' => false,
        ])->push([
            'url' => $this->nextPageUrl(),
            'label' => function_exists('__') ? __('pagination.next') : 'Next',
            'active' => false,
        ]);
    }
    protected function elements()
    {
        $window = UrlWindow::make($this);
        
        return array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]);
    }
    public function total()
    {
        return $this->total;
    }
    public function hasMorePages()
    {
        return $this->currentPage() < $this->lastPage();
    }
    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }
    }
    public function lastPage()
    {
        return $this->lastPage;
    }
    public function firstPage()
    {
        return 1;
    }
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'links' => $this->linkCollection()->toArray(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
        ];
    }
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
