<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ResourceModel extends Model
{
    protected $context = null;
    
    public function hasContext()
    {
        return !empty($this->context);
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext(array $context)
    {
        $this->context = $context;
    }

    public function addToContext(string $key, $value)
    {
        if (is_null($this->context)) {
            $this->context = [];
        }
        $this->context[$key] = $value;
    }
}
