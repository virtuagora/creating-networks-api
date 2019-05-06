<?php

namespace App\Util;

use Carbon\Carbon;

class Paginator
{
    protected $items;
    protected $total;
    protected $size;
    protected $offset;
    protected $params;
    protected $uri;

    public function __construct($query, $params, $uri = null)
    {
        $this->params = $params;
        $this->total = $query->toBase()->getCountForPagination();
        $this->size = $params['size'];
        $this->offset = $params['offset'];
        $this->items = $query->offset($params['offset'])->take($params['size'])->get();
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
            $links['prev'] = min(0, $this->urlWithOffset($this->offset - $this->size));
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
