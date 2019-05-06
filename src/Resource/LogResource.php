<?php

namespace App\Resource;

use App\Util\ContainerClient;
use App\Util\Exception\AppException;

class LogResource extends ContainerClient
{
    public function createOne($subject, $data) {
        $log = $this->db->newInstance('App:Log', $data);
        $log->subject_id = $subject->getId();
        if (isset($data['object'])) {
            $log->object()->associate($data['object']);
        }
        if (isset($data['action'])) {
            $log->action_id = $data['action'];
        }
        $log->save();
        return $log;
    }
}
