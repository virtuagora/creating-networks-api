<?php

namespace App\Util;

use Illuminate\Contracts\View\View as ViewContract;

class View implements ViewContract
{
    protected $renderer;
    protected $view;
    protected $data;
 
    public function __construct($renderer, $view, $data = [])
    {
        $this->renderer = $renderer;
        $this->view = $view;
        $this->data = $data;
    }

    public function render()
    {
        return $this->renderer->fetch($this->view, $this->data);
    }

    public function name()
    {
        return $this->view;
    }
    
    public function with($key, $value = null)
    {
        $this->data = array_merge($this->data, $key);
        return $this;
    }
}