<?php

namespace App\Migration;

use Grimzy\LaravelMysqlSpatial\Schema\Blueprint;

class Release003Migration
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
        $action = $this->db->query('App:Action')->find('associateInitiativeCountry');
        return isset($action);
    }

    public function up()
    {
        $this->schema->table('spaces', function($t) {
            $t->lineString('line_string')->nullable();
            $t->multiPoint('multi_point')->nullable();
            $t->multiLineString('multi_line_string')->nullable();
            $t->multiPolygon('multi_polygon')->nullable();
            $t->string('type')->default('Point');
            $t->dropColumn('line');
        });
        $this->schema->table('countries', function($t) {
            $t->integer('initiatives_count')->unsigned()->default(0);
        });
    }

    public function down()
    {
        $this->schema->table('spaces', function($t) {
            $t->lineString('line')->nullable();
            $t->dropColumn('line_string');
            $t->dropColumn('multi_point');
            $t->dropColumn('multi_line_string');
            $t->dropColumn('multi_polygon');
            $t->dropColumn('type');
        });
        $this->schema->table('countries', function($t) {
            $t->dropColumn('initiatives_count');
        });
    }

    public function populate()
    {
    }

    public function updateActions()
    {
        $this->db->table('actions')->insert([
            ['id' => 'associateInitiativeCountry', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
        ]);
    }
}
