<?php

namespace App\Migration;

use Grimzy\LaravelMysqlSpatial\Schema\Blueprint;

class Release002Migration
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
        $action = $this->db->query('App:Action')->find('updateUser');
        return isset($action);
    }

    public function up()
    {

    }

    public function down()
    {
        $this->db->query('App:Action')
            ->whereIn('id', ['updateUser', 'deleteTerm'])
            ->delete();
    }

    public function populate()
    {
        $today = \Carbon\Carbon::now();
    }

    public function updateActions()
    {
        $this->db->table('actions')->insert([
            ['id' => 'updateUser', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'deleteTerm', 'group' => 'term', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
        ]);
    }
}
