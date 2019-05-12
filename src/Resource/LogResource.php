<?php

namespace App\Resource;

use App\Util\ContainerClient;
use App\Util\Exception\AppException;

class LogResource extends ContainerClient
{
    public function createLog($subject, $data) {
        $log = $this->db->create('App:Log', $data);
        $log->subject_id = $subject->id;
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
