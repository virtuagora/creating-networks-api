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
        $iniType = $this->db->query('App:GroupType')->find('Initiative');
        $iniType->public_schema = [
            'type' => 'object',
            'properties' => [
                'founding_year' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 2020,
                ],
                'goals' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 750,
                ],
                'website' => [
                    'type' => 'string',
                    'minLength' => 3,
                    'maxLength' => 100,
                ],
                'facebook' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 100,
                ],
                'twitter' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 100,
                ],
                'other_network' => [
                    'type' => 'string',
                    'minLength' => 3,
                    'maxLength' => 100,
                ],
                'role_of_youth' => [
                    'type' => 'string',
                    'enum' => [
                        'targetAudience', 'leadership', 'membership', 'volunteer',
                    ],
                ],
                'interested_in_participate' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
            'required' => [
                'founding_year', 'goals', 'role_of_youth',
            ],
            'additionalProperties' => false,
        ];
        $iniType->save();
    }

    public function down()
    {
        $this->db->query('App:Action')
            ->whereIn('id', ['updateInitiative', 'associateSubjectGroup'])
            ->delete();
    }

    public function populate()
    {
        $today = \Carbon\Carbon::now();
    }

    public function updateActions()
    {
        $this->db->table('actions')->insert([
            ['id' => 'updateInitiative', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'associateSubjectGroup', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
        ]);
    }
}
