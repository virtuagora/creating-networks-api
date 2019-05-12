<?php

namespace App\Auth;

class DummySubject implements SubjectInterface, ObjectInterface
{
    public $id;
    public $type;
    public $display_name;
    protected $roles;
    protected $extra;

    public function __construct($type, $id = null, $name = null, $roles = [], $extra = [])
    {
        $this->type = $type;
        $this->id = $id;
        $this->display_name = $name;
        $this->roles = $roles;
        $this->extra = $extra;
    }

    public function __get($key)
    {
        return $this->extra[$key] ?? null;
    }

    public function toArray()
    {
        return array_merge($this->extra, [
            'id' => $this->id,
            'type' => $this->type,
            'display_name' => $this->display_name,
            'roles_list' => $this->roles,
        ]);
    }

    public function rolesList()
    {
        return $this->roles;
    }

    public function relationsWith(SubjectInterface $subject)
    {
        return (isset($this->id) && $this->id == $subject->id) ? ['self'] : [];
    }
}
