<?php

namespace App\Util;

use Illuminate\Contracts\View\Factory as FactoryContract;

class ViewFactory implements FactoryContract
{
    protected $renderer;
 
    public function __construct($renderer)
    {
        $this->renderer = $renderer;
    }

    public function exists($view)
    {
        return true;
    }
    
    public function file($path, $data = [], $mergeData = [])
    {
        return "";
    }
   
    public function make($view, $data = [], $mergeData = [])
    {
        return new View($this->renderer, $view, $data);
    }
    
    public function share($key, $value = null){
        return null;
    }
    
    public function composer($views, $callback)
    {
        return [];
    }
    
    public function creator($views, $callback)
    {
        return [];
    }
    
    public function addNamespace($namespace, $hints)
    {
        return $this;
    }
    
    public function replaceNamespace($namespace, $hints)
    {
        return $this;
    }
}
