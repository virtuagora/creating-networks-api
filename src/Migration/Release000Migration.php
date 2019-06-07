<?php

namespace App\Migration;

use Grimzy\LaravelMysqlSpatial\Schema\Blueprint;

class Release000Migration
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
        return $this->schema->hasTable('options');
    }

    public function up()
    {
        $this->schema->create('spaces', function (Blueprint $t) {
            $t->increments('id');
            $t->point('point');
            $t->lineString('line')->nullable();
            $t->polygon('polygon')->nullable();
            $t->timestamps();
            $t->spatialIndex('point');
        });
        $this->schema->create('regions', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('name');
            $t->json('data')->nullable();
            $t->json('localization')->nullable();
            $t->integer('space_id')->unsigned()->nullable();
            $t->foreign('space_id')->references('id')->on('spaces')->onDelete('set null');
            $t->timestamps();
        });
        $this->schema->create('countries', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('name');
            $t->string('code_2');
            $t->string('code_3');
            $t->json('data')->nullable();
            $t->json('localization')->nullable();
            $t->integer('region_id')->unsigned();
            $t->foreign('region_id')->references('id')->on('regions')->onDelete('restrict');
            $t->integer('space_id')->unsigned();
            $t->foreign('space_id')->references('id')->on('spaces')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('cities', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('name');
            $t->json('data')->nullable();
            $t->json('localization')->nullable();
            $t->integer('initiatives_count')->unsigned()->default(0);
            $t->integer('country_id')->unsigned();
            $t->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
            $t->integer('space_id')->unsigned();
            $t->foreign('space_id')->references('id')->on('spaces')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('registered_cities', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('name');
            $t->point('point');
            $t->string('trace')->nullable();
            $t->json('localization')->nullable();
            $t->integer('country_id')->unsigned();
            $t->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
            $t->integer('city_id')->unsigned()->nullable();
            $t->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
        $this->schema->create('options', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->string('type'); //integer, string, text, hidden
            $t->string('group');
            $t->boolean('autoload');
            $t->timestamps();
        });
        $this->schema->create('roles', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name')->unique();
            $t->boolean('show_badge');
            $t->string('icon')->nullable();
            $t->json('data')->nullable();
            $t->timestamps();
        });
        $this->schema->create('people', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('email')->nullable();
            $t->string('facebook')->nullable();
            $t->string('phone')->nullable();
            $t->string('person_id')->nullable();
            $t->string('names');
            $t->string('surnames');
            $t->string('trace')->nullable();
            $t->timestamps();
            $t->index('email');
            $t->index('facebook');
            $t->index('phone');
        });
        $this->schema->create('group_types', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name')->unique();
            $t->text('description');
            $t->json('role_policy')->nullable(); // enum(single, group, empty, custom)
            $t->json('allowed_relations')->nullable(); // list of allowed relations for every group
            $t->json('public_schema');
            $t->json('private_schema');
            $t->string('role_id')->nullable(); // role for the groups of this type
            $t->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('groups', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('name');
            $t->text('description');
            $t->integer('quota')->unsigned()->nullable();
            $t->json('public_data')->nullable();
            $t->json('private_data')->nullable();
            $t->string('trace')->nullable();
            $t->integer('city_id')->unsigned()->nullable();
            $t->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $t->integer('parent_id')->unsigned()->nullable();
            $t->foreign('parent_id')->references('id')->on('groups')->onDelete('set null');
            $t->string('group_type_id');
            $t->foreign('group_type_id')->references('id')->on('group_types')->onDelete('restrict');
            $t->timestamps();
        });
        $this->schema->create('subjects', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('username')->unique();
            $t->string('password')->nullable();
            $t->timestamp('ban_expiration')->nullable();
            $t->string('display_name');
            $t->text('bio')->nullable();
            $t->json('data')->nullable();
            $t->integer('img_type')->unsigned();
            $t->string('img_hash');
            $t->integer('points')->default(0);
            $t->string('trace')->nullable();
            $t->string('type');
            $t->string('locale')->default('en');
            $t->integer('person_id')->unsigned()->nullable();
            $t->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
            $t->integer('group_id')->unsigned()->nullable();
            $t->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('subject_group', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('relation');
            $t->string('title')->nullable();
            $t->integer('subject_id')->unsigned();
            $t->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $t->integer('group_id')->unsigned();
            $t->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });
        $this->schema->create('subject_role', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->timestamp('expires_on')->nullable();
            $t->integer('subject_id')->unsigned();
            $t->string('role_id');
            $t->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $t->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
        $this->schema->create('tokens', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('token')->unique();
            $t->string('type');
            $t->json('data')->nullable();
            $t->string('finder')->nullable();
            $t->integer('subject_id')->unsigned()->nullable();
            $t->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $t->timestamp('expires_on')->nullable();
            $t->timestamps();
            $t->index('finder');
        });
        $this->schema->create('node_types', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('name');
            $t->text('description');
            $t->json('subtypes')->nullable(); // with list of allowed relations for every subtype
            $t->json('public_schema');
            $t->json('private_schema');
            $t->timestamps();
        });
        $this->schema->create('nodes', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('title');
            $t->string('type');
            $t->text('content')->nullable();
            $t->integer('points')->default(0);
            $t->dateTime('close_date')->nullable();
            $t->boolean('unlisted');
            $t->string('trace')->nullable();
            $t->json('data')->nullable();
            $t->integer('author_id')->unsigned();
            $t->foreign('author_id')->references('id')->on('subjects')->onDelete('cascade');
            $t->integer('space_id')->unsigned();
            $t->foreign('space_id')->references('id')->on('spaces')->onDelete('cascade');
            $t->timestamps();
            $t->index('type');
        });
        $this->schema->create('node_node', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('relation')->nullable();
            $t->integer('parent_id')->unsigned();
            $t->foreign('parent_id')->references('id')->on('nodes')->onDelete('cascade');
            $t->integer('child_id')->unsigned();
            $t->foreign('child_id')->references('id')->on('nodes')->onDelete('cascade');
        });
        $this->schema->create('node_subject', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('relation');
            $t->integer('value')->nullable();
            $t->integer('node_id')->unsigned();
            $t->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
            $t->integer('subject_id')->unsigned();
            $t->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
        $this->schema->create('comments', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->text('content');
            $t->integer('votes')->default(0);
            $t->json('data')->nullable();
            $t->integer('node_id')->unsigned();
            $t->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
            $t->integer('author_id')->unsigned();
            $t->foreign('author_id')->references('id')->on('subjects')->onDelete('cascade');
            $t->integer('parent_id')->unsigned()->nullable();
            $t->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            $t->timestamps();
        });
        // $this->schema->create('comment_votes', function (Blueprint $t) {
        //     $t->engine = 'InnoDB';
        //     $t->increments('id');
        //     $t->integer('value');
        //     $t->integer('subject_id')->unsigned();
        //     $t->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        //     $t->integer('comment_id')->unsigned();
        //     $t->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
        //     $t->timestamps();
        // });
        $this->schema->create('taxonomies', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();;
            $t->string('name');
            $t->text('description');
            $t->json('schema');
            $t->json('rules');
            $t->json('localization')->nullable();
            $t->timestamps();
        });
        $this->schema->create('terms', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->string('name');
            $t->string('trace');
            $t->json('data')->nullable();
            $t->json('localization')->nullable();
            $t->integer('count')->unsigned()->default(0);
            $t->string('taxonomy_id');
            $t->foreign('taxonomy_id')->references('id')->on('taxonomies')->onDelete('cascade');
            $t->timestamps();
        });
        $this->schema->create('term_object', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->integer('term_id')->unsigned();
            $t->string('object_type');
            $t->integer('object_id')->unsigned();
            $t->json('data')->nullable();
            $t->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $t->index(['object_type', 'object_id']);
            $t->timestamps();
        });
        $this->schema->create('actions', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->string('id')->primary();
            $t->string('group');
            $t->string('allowed_roles');
            $t->string('allowed_relations');
            $t->string('allowed_proxies');
            $t->integer('points')->nullable();
            $t->timestamps();
        });
        // $this->schema->create('pages', function (Blueprint $t) {
        //     $t->engine = 'InnoDB';
        //     $t->increments('id');
        //     $t->string('name');
        //     $t->string('link')->nullable();
        //     $t->json('data')->nullable();
        //     $t->string('slug');
        //     $t->integer('order')->default(0);
        //     $t->integer('parent_id')->unsigned()->nullable();
        //     $t->foreign('parent_id')->references('id')->on('pages')->onDelete('set null');
        //     $t->integer('node_id')->unsigned()->nullable();
        //     $t->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
        // });
        $this->schema->create('logs', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->integer('subject_id')->unsigned()->nullable();
            $t->integer('proxy_id')->unsigned()->nullable();
            $t->string('action_id');
            $t->string('object_type');
            $t->integer('object_id')->unsigned();
            $t->json('parameters')->nullable();
            $t->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $t->foreign('proxy_id')->references('id')->on('subjects')->onDelete('set null');
            $t->foreign('action_id')->references('id')->on('actions')->onDelete('cascade');
            $t->index(['object_type', 'object_id']);
            $t->timestamps();
        });
        // $this->schema->create('notifications', function (Blueprint $t) {
        //     $t->engine = 'InnoDB';
        //     $t->increments('id');
        //     $t->boolean('seen')->default(false);
        //     $t->integer('log_id')->unsigned();
        //     $t->foreign('log_id')->references('id')->on('logs')->onDelete('cascade');
        //     $t->integer('subject_id')->unsigned();
        //     $t->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        // });

        // --- Plugin content ballots ---

        $this->schema->create('ballots', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->increments('id');
            $t->json('options');
            $t->integer('total_votes')->unsigned()->default(0);
            $t->boolean('secret')->default(false);
            $t->integer('node_id')->unsigned();
            $t->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->dropAllTables();
    }

    public function populate()
    {
        $today = \Carbon\Carbon::now();

        $this->db->table('roles')->insert([
            [
                'id' => 'User',
                'name' => 'User',
                'show_badge' => false,
            ], [
                'id' => 'Verified',
                'name' => 'Verified user',
                'show_badge' => true,
            ], [
                'id' => 'Admin',
                'name' => 'Admnistrator',
                'show_badge' => true,
            ], [
                'id' => 'StaffGroup',
                'name' => 'Staff group',
                'show_badge' => false,
            ], [
                'id' => 'InitiativeGroup',
                'name' => 'Initiative group',
                'show_badge' => false,
            ],
        ]);

        $this->db->createAndSave('App:GroupType', [
            'id' => 'Staff',
            'name' => 'Staff',
            'description' => 'Administration teams',
            'role_id' => 'StaffGroup',
            'public_schema' => [
                'type' => 'null',
            ],
            'private_schema' => [
                'type' => 'null',
            ],
        ]);
        $this->db->createAndSave('App:GroupType', [
            'id' => 'Initiative',
            'name' => 'Initiative',
            'description' => 'Youth initiatives',
            'role_id' => 'InitiativeGroup',
            'allowed_relations' => [
                'owner' => [
                    'name' => 'Owner',
                ],
            ],
            'public_schema' => [
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
                        'maxLength' => 500,
                    ],
                    'website' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 100,
                    ],
                    'facebook' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 100,
                    ],
                    'twitter' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 100,
                    ],
                    'other_network' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 100,
                    ],
                    'role_of_youth' => [
                        'type' => 'string',
                        'enum' => [
                            'targetAudience', 'leadership', 'membership'
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
            ],
            'private_schema' => [
                'type' => 'object',
                'properties' => [
                    'contact_email' => [
                        'type' => 'string',
                        'minLength' => 5,
                        'maxLength' => 100,
                        'format' => 'email',
                    ],
                    'contact_phone' => [
                        'type' => 'string',
                        'minLength' => 5,
                        'maxLength' => 20,
                    ],
                ],
                'required' => [
                    'contact_email',
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->db->createAndSave('App:Taxonomy', [
            'id' => 'topics',
            'name' => 'Topics',
            'description' => 'ICT and Internet related topics which are of interest or concern',
            'rules' => [
                'habtm' => true, // HasAndBelongsToMany
            ],
            'schema' => [
                'type' => 'null',
            ],
        ]);

        $this->db->table('terms')->insert([
            ['name' => 'Access for people with disabilities', 'trace' => 'Accessforpeoplewithdisabilities', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Access to Information', 'trace' => 'AccesstoInformation', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Cybersecurity and Data Protection', 'trace' => 'CybersecurityandDataProtection', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'LGBT+ Community', 'trace' => 'LGBTCommunity', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Entrepreneurship', 'trace' => 'Entrepreneurship', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Arts and Culture', 'trace' => 'ArtsandCulture', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Democracy', 'trace' => 'Democracy', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Human Rights', 'trace' => 'HumanRights', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Gender Equality', 'trace' => 'GenderEquality', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Social Inclusion and Inequality', 'trace' => 'SocialInclusionandInequality', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Technological Innovation', 'trace' => 'TechnologicalInnovation', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Internet of Things', 'trace' => 'InternetofThings', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Freedom of Expression', 'trace' => 'FreedomofExpression', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Religious Freedom', 'trace' => 'ReligiousFreedom', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Fight Against Racism', 'trace' => 'FightAgainstRacism', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Net Neutrality', 'trace' => 'NetNeutrality', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Academic Research', 'trace' => 'AcademicResearch', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Community Networks', 'trace' => 'CommunityNetworks', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Health', 'trace' => 'Health', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
            ['name' => 'Transparency and Government Accountability', 'trace' => 'TransparencyandGovernmentAccountability', 'taxonomy_id' => 'topics', 'created_at' => $today, 'updated_at' => $today],
        ]);

    //     $this->db->table('users')->insert([
    //         [
    //             'email' => 'admin@rutatrans.org',
    //             'names' => 'Augusto',
    //             'surnames' => 'Mathurin',
    //             'password' => password_hash('123', PASSWORD_DEFAULT),
    //             'created_at' => $today,
    //             'updated_at' => $today,
    //             'trace' => 'augustomathurin',
    //         ],
    //     ]);

    //     $this->db->table('subjects')->insert([
    //         [
    //             'display_name' => 'Augusto Mathurin',
    //             'img_type' => '0',
    //             'img_hash' => '0',
    //             'points' => 0,
    //             'type' => 'User',
    //             'user_id' => 1,
    //             'updated_at' => $today,
    //             'created_at' => $today,
    //         ],
    //     ]);

    //     $this->db->table('subject_role')->insert([
    //         [
    //             'subject_id' => 1,
    //             'role_id' => 'user',
    //         ], [
    //             'subject_id' => 1,
    //             'role_id' => 'admin',
    //         ],
    //     ]);
    }

    public function updateActions()
    {
        $this->db->table('actions')->insert([
            ['id' => 'updateUserPassword', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["self"]', 'allowed_proxies' => '[]'],
            ['id' => 'createInitiative', 'group' => 'initiative', 'allowed_roles' => '["User"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
            ['id' => 'createRegisteredCity', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
            ['id' => 'createTerm', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
            ['id' => 'associateInitiativeTerm', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'deleteInitiative', 'group' => 'initiative', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '["owner"]', 'allowed_proxies' => '[]'],
            ['id' => 'associateSubjectRole', 'group' => 'user', 'allowed_roles' => '["Admin"]', 'allowed_relations' => '[]', 'allowed_proxies' => '[]'],
        ]);
    }
}
