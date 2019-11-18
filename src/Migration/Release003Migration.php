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
        $this->schema->table('spaces', function(Blueprint $t) {
            $t->lineString('line_string')->nullable();
            $t->multiPoint('multi_point')->nullable();
            $t->multiLineString('multi_line_string')->nullable();
            $t->multiPolygon('multi_polygon')->nullable();
            $t->string('type')->default('Point');
            $t->dropColumn('line');
        });
        $this->schema->table('countries', function(Blueprint $t) {
            $t->integer('initiatives_count')->unsigned()->default(0);
        });
        $this->schema->table('groups', function(Blueprint $t) {
            $t->json('pictures')->nullable();
        });
        $this->schema->create('group_country', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->integer('group_id')->unsigned();
            $t->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $t->integer('country_id')->unsigned();
            $t->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
        $iniType = $this->db->query('App:GroupType')->find('Initiative');
        $iniType->allowed_relations = [
            'owner' => [
                'name' => 'Owner',
            ],
            'member' => [
                'name' => 'Member',
                'lower_relation' => 'follower',
            ],
            'follower' => [
                'name' => 'Follower',
            ],
        ];
        $iniType->save();
        $this->db->query('App:Action')->where('id', 'updateUser')->delete();
    }

    public function down()
    {
        $this->db->query('App:Action')
            ->whereIn('id', [
                'associateInitiativeCountry', 'updateInitiativeMember',
                'joinInitiative', 'leaveInitiative',
            ])->delete();
        $this->schema->table('spaces', function(Blueprint $t) {
            $t->lineString('line')->nullable();
            $t->dropColumn('line_string');
            $t->dropColumn('multi_point');
            $t->dropColumn('multi_line_string');
            $t->dropColumn('multi_polygon');
            $t->dropColumn('type');
        });
        $this->schema->table('countries', function(Blueprint $t) {
            $t->dropColumn('initiatives_count');
        });
        $this->schema->table('groups', function(Blueprint $t) {
            $t->dropColumn('pictures');
        });
        $this->schema->dropIfExists('group_country');
    }

    public function populate()
    {
        $loader = new \App\Util\DataLoader($this->db);
        $loader->createCountrySpaces();
    }

    public function updateActions()
    {
        $this->db->table('actions')->insert([
            ['id' => 'updateUser', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["self"]', 'allowed_proxies' => '[]'],
            ['id' => 'associateInitiativeCountry', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'joinInitiative', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["self"]', 'allowed_proxies' => '[]'],
            ['id' => 'leaveInitiative', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["self"]', 'allowed_proxies' => '[]'],
            ['id' => 'updateInitiativeMember', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
        ]);
    }
}
