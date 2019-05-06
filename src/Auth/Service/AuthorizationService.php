<?php

namespace App\Auth\Service;

use App\Util\Exception\UnauthorizedException;
use App\Auth\SubjectInterface as Subject;
use App\Auth\ObjectInterface as Object;

class AuthorizationService
{
    protected $db;
    protected $logger;

    public function __construct($db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function check(Subject $subject, string $actName, Object $object = null, $proxy = null)
    {
        $action = $this->db->query('App:Action')->find($actName);
        if (is_null($action)) {
            return false;
        }
        $this->logger->info(json_encode([$subject->toArray(), $action->allowed_roles]));
        $subRoles = $subject->rolesList();
        if (array_intersect($subRoles, $action->allowed_roles)) {
            return true;
        }
        if (isset($object)) {
            // $this->logger->info(json_encode([$object->relationsWith($subject), $action->allowed_relations]));
            $objRelations = $object->relationsWith($subject);
            return array_intersect($objRelations, $action->allowed_relations);
        }
        //TODO verificar proxies
        return false;
    }

    public function checkOrFail(Subject $subject, string $action, Object $object = null, $proxy = null)
    {
        if (!$this->check($subject, $action, $object, $proxy)) {
            throw new UnauthorizedException();
        }
    }
}
