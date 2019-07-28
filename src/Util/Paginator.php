<?php

namespace App\Util;

use Carbon\Carbon;

class Paginator
{
    protected $items;
    protected $total;
    protected $size;
    protected $offset;
    protected $sort;
    protected $params;
    protected $uri;

    public function __construct($query, $params, $uri = null)
    {
        $this->params = $params;
        $this->total = $query->toBase()->getCountForPagination();
        $this->size = $params['size'];
        $this->offset = $params['offset'];
        $this->sort = $params['sort'] ?? null;
        if ($this->sort == 'random') {
            if ($this->total < 4 * $this->size) {
                $this->items = $query->inRandomOrder()->take($this->size)->get();
            } else {
                $take = $this->size - 1;
                $ceil = $this->total;
                $bseQ = (clone $query)->offset(rand(0, $ceil))->take(1);
                for ($i = 0; $i < $take; $i++) {
                    $auxQ = (clone $query)->offset(rand(0, $ceil))->take(1);
                    $bseQ->union($auxQ);
                }
                $this->items = $bseQ->get();
            }
        } else {
            $this->items = $query->offset($this->offset)->take($this->size)->get();
        }
        $this->uri = $uri;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function addItem($new)
    {
        return $this->items->push($new);
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function hasMorePages()
    {
        return $this->offset + $this->size < $this->total;
    }

    public function urlWithOffset($offset)
    {
        $params = $this->params;
        $params['offset'] = $offset;
        if (isset($this->uri)) {
            return ''.$this->uri->withQuery(http_build_query($params));
        } else {
            return http_build_query($params);
        }
    }

    public function getPaginationInfo()
    {
        return [
            'offset' => $this->offset,
            'size' => $this->size,
            'total' => $this->total,
        ];
    }

    public function getLinks()
    {
        $links = [];
        if ($this->hasMorePages()) {
            $links['next'] = $this->urlWithOffset($this->offset + $this->size);
        }
        if ($this->offset > 0) {
            $links['prev'] = $this->urlWithOffset(min(0, $this->offset - $this->size));
        }
        return $links;
    }

    public function toArray()
    {
        return [
            'pagination' => $this->getPaginationInfo(),
            'data' => $this->items->toArray(),
            'links' => $this->getLinks(),
        ];
    }
}
