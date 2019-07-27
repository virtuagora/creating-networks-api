<?php

namespace App\Migration;

use Grimzy\LaravelMysqlSpatial\Schema\Blueprint;

class Release001Migration
{
    protected $db;
    protected $schema;
    
    public function __construct($db)
    {
        $this->db = $db;
        $this->schema = $db->schema();
        $this->schema->blueprintResolver(function($t, $callback) {
            return new Blueprint($t, $callback);
        });
    }

    public function isInstalled()
    {
        $action = $this->db->query('App:Action')->find('updateInitiative');
        return isset($action);
    }

    public function up()
    {
        return;
    }

    public function down()
    {
        return;
    }

    public function populate()
    {
        $today = \Carbon\Carbon::now();
        return;
    }

    public function updateActions()
    {
        $this->db->table('actions')->insert([
            ['id' => 'updateInitiative', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'associateSubjectGroup', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
        ]);
    }
}
