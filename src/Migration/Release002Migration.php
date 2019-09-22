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
        $this->schema->table('nodes', function($t) {
            $t->boolean('unlisted')->default(false)->change();
            $t->dropColumn('type');
            $t->string('node_type_id');
            $t->foreign('node_type_id')->references('id')->on('node_types')->onDelete('restrict');
        });
    }

    public function down()
    {
        $this->db->query('App:Action')
            ->whereIn('id', ['updateUser', 'deleteTerm', 'associateUserTerm'])
            ->delete();
        $this->schema->table('nodes', function($t) {
            $t->dropColumn('node_type_id');
            $t->string('type');
        });
    }

    public function populate()
    {
        $today = \Carbon\Carbon::now();
        $this->db->createAndSave('App:NodeType', [
            'id' => 'Video',
            'name' => 'Video',
            'description' => 'Videos',
            'public_schema' => [
                'type' => 'object',
                'properties' => [
                    'youtube' => [
                        'type' => 'string',
                        'minLength' => 8,
                        'maxLength' => 100,
                    ],
                ],
                'required' => [
                    'youtube',
                ],
                'additionalProperties' => false,
            ],
            'private_schema' => [
                'type' => 'null',
            ],
        ]);
    }

    public function updateActions()
    {
        $this->db->table('actions')->insert([
            ['id' => 'updateUser', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'deleteTerm', 'group' => 'term', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
            ['id' => 'associateUserTerm', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["self"]', 'allowed_proxies' => '[]'],
            ['id' => 'createVideo', 'group' => 'video', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
            ['id' => 'updateVideo', 'group' => 'video', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["author"]', 'allowed_proxies' => '[]'],
            ['id' => 'deleteVideo', 'group' => 'video', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["author"]', 'allowed_proxies' => '[]'],
        ]);
    }
}
