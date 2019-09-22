<?php

namespace App\Resource;

use App\Mail\SignUpEmail;
use App\Mail\PasswordResetEmail;
use App\Util\Exception\AppException;
use App\Util\Exception\UnauthorizedException;
use App\Util\Utils;
use App\Util\Paginator;
use Carbon\Carbon;

class UserResource extends Resource
{
    public function retrieveSchema($options = [])
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'names' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 25,
                ],
                'surnames' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 25,
                ],
                'password' => [
                    'type' => 'string',
                    'minLength' => 4,
                    'maxLength' => 250,
                ],
                // 'email' => [
                //     'type' => 'string',
                //     'format' => 'email',
                // ],
                // 'token' => [
                //     'type' => 'string',
                //     'minLength' => 10,
                //     'maxLength' => 100,
                // ],
            ],
            'required' => ['names', 'surnames', 'password'],
            'additionalProperties' => false,
        ];
        if (isset($options['edit'])) {
            $schema['properties'] = [
                'bio' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 750,
                ],
                'data' => [
                    'type' => 'object',
                    'properties' => [
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
                    ],
                    'additionalProperties' => false,
                ],
            ];
            $schema['required'] = [];
            $schema = $this->validation->prepareSchema($schema);
        }
        return $schema;
    }

    public function createUser($subject, $data, $options = [], $flags = 0)
    {
        $datSch = $this->retrieveSchema();
        $v = $this->validation->fromSchema($datSch);
        $data = $this->validation->prepareData($datSch, $data, true);
        $v->assert($data);
        $optSch = [
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 100,
                ],
            ],
            'required' => ['token'],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($optSch);
        $options = $this->validation->prepareData($optSch, $options, true);
        $v->assert($options);
        $user = $this->identity->signUp('local', $options['token'], $data);
        $adminEmails = $this->settings['adminEmails'] ?? [];
        if (in_array($user->username, $adminEmails)) {
            $user->roles()->attach('Admin');
        }
        return $user;
    }

    public function retrieveUser($subject, $id, $options = [])
    {
        return $this->db->query('App:Subject', ['person.terms'])
            ->where('type', 'User')
            ->findOrFail($id);
    }

    public function updateUser($subject, $usrId, $data, $options = [], $flags = 3)
    {
        $user = $this->db->query('App:Subject')
            ->where('type', 'User')
            ->findOrFail($usrId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateUser', $user
            );
        }
        $schema = $this->retrieveSchema(['edit' => true]);
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $v->assert($data);
        $user->fill($data);
        $user->save();
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'updateUser',
                'object' => $user,
            ]);
        }
        return $user;
    }

    public function retrieveSubjects($subject, $options = [])
    {
        $pagSch = $this->helper->getPaginatedQuerySchema([
            'username' => [
                'type' => 'string',
            ],
            'role' => [
                'type' => 'string',
                'enum' => ['Admin', 'User'],
            ],
            's' => [
                'type' => 'string',
            ],
        ]);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $this->db->query('App:Subject');
        if (isset($options['username'])) {
            $query->where('username', $options['username']);
        }
        if (isset($options['role'])) {
            $query->whereHas('roles', function ($q) use ($options) {
                $q->where('role_id', $options['role']);
            });
        }
        if (isset($options['s'])) {
            $filter = Utils::traceStr($options['s']);
            $query->where('trace', 'LIKE', "%$filter%");
        }
        return new Paginator($query, $options);
    }

    public function createPendingUser($subject, $data)
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                ],
                'locale' => [
                    'type' => 'string',
                    'enum' => ['en', 'es'],
                    'default' => 'en',
                ],
            ],
            'required' => ['email'],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data, true);
        $v->assert($data);
        $dupFields = $this->db->findDuplicatedFields('App:Person', [
            'email' => $data['email']
        ]);
        if (count($dupFields) > 0) {
            throw new AppException(
                'Email already registered', 'emailAlreadyExists'
            );
        }
        $pending = $this->db->query('App:Token')->firstOrNew([
            'type' => 'signUp',
            'finder' => $data['email'],
        ]);
        $pending->token = Utils::randomStr(50);
        $pending->data = [
            'email' => $data['email'],
            'locale' => $data['locale'],
        ];
        $pending->save();
        $link = $this->settings['spaUrl'];
        if ($data['locale'] != 'en') {
            $link .= '/' . $data['locale'];
        }
        $link .= '/#/auth/complete-signup/' . $pending->token;
        $mailArg = [
            'acceso' => $pending->finder,
            'link' => $link,
        ];
        $mail = new SignUpEmail($mailArg);
        $mail->setLocale($data['locale']);
        $this->mailer->to($pending->finder)->send($mail);
        return $pending;
    }

    public function createResetToken($subject, $data)
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                ],
            ],
            'required' => ['email'],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data, true);
        $v->assert($data);
        $person = $this->db->query('App:Person', ['subject'])
            ->where('email', $data['email'])
            ->firstOrFail();
        $subject = $person->subject;
        $token = $this->db->create('App:Token', [
            'type' => 'resetPassword',
            'expires_on' => Carbon::now()->addDay(),
            'subject_id' => $subject->id,
            'token' => 'resetTkn' . Utils::randomStr(42),
        ]);
        $token->save();
        $link = $this->settings['spaUrl'];
        if ($subject->locale != 'en') {
            $link .= '/' . $subject->locale;
        }
        $link .= '/#/auth/complete-recover/' . $subject->id . '/' . $token->token;
        $mailArg = [
            'link' => $link,
        ];
        $mail = new PasswordResetEmail($mailArg);
        $mail->setLocale($subject->locale);
        $this->mailer->to($person->email)->send($mail);
        return $token;
    }

    public function updatePassword($subject, $usrId, $data, $options = [], $flags = 3)
    {
        // TODO limitar a solo users;
        $user = $this->db->query('App:Subject')->findOrFail($usrId);
        if (!isset($options['token']) && $flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'updateUserPassword', $user
            );
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'password' => [
                    'type' => 'string',
                    'minLength' => 4,
                    'maxLength' => 250,
                ],
            ],
            'required' => ['password'],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data, true);
        $v->assert($data);
        $optSchema = [
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'minLength' => 50,
                    'maxLength' => 50,
                ],
                'current_password' => [
                    'type' => 'string',
                    'minLength' => 4,
                    'maxLength' => 250,
                ],
            ],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($optSchema);
        $options = $this->validation->prepareData($optSchema, $options, true);
        $v->assert($options);
        if (isset($options['token'])) {
            $token = $this->db->query('App:Token')
                ->where('token', $options['token'])
                ->where('subject_id', $usrId)
                ->first();
            if (is_null($token)) {
                throw new AppException(
                    'Invalid token', 'invalidToken'
                );
            }
            if ($token->expires_on->lt(Carbon::now())) {
                $token->delete();
                throw new AppException(
                    'Expired token', 'expiredToken'
                );
            }
            $token->delete();
        } elseif (isset($options['current_password'])) {
            if (!password_verify($options['current_password'], $user->password)) {
                throw new AppException(
                    'Invalid password', 'invalidPassword'
                );
            }
        } else {
            throw new UnauthorizedException();
        }
        $user->password = $data['password'];
        $user->save();
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'updateUserPassword',
                'object' => $user,
            ]);
        }
    }

    public function attachRole($subject, $subId, $rolId, $data, $flags = 3)
    {
        $subj = $this->db->query('App:Subject')->findOrFail($subId);
        $role = $this->db->query('App:Role')->findOrFail($rolId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateSubjectRole', $subj
            );
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'expires_on' => [
                    'type' => 'string',
                    'format' => 'date',
                ],
            ],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data, true);
        $v->assert($data);
        $exp = isset($data['expires_on']) ? new Carbon($data['expires_on']) : null;
        $changes = $subj->roles()->syncWithoutDetaching([
            $rolId => ['expires_on' => $exp],
        ]);
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'associateSubjectRole',
                'object' => $subj,
            ]);
        }
        return count($changes['attached']);
    }

    public function retrieveGroups($subject, $id, $options = [])
    {
        $user = $this->db->query('App:Subject')
            ->where('type', 'User')
            ->findOrFail($id);

        $pagSch = $this->helper->getPaginatedQuerySchema([
            'type' => [
                'type' => 'string',
                'enum' => ['Initiative'],
            ],
            'relation' => [
                'type' => 'string',
                'enum' => ['owner'],
            ],
        ]);
        $v = $this->validation->fromSchema($pagSch);
        $options = $this->validation->prepareData($pagSch, $options, true);
        $v->assert($options);
        $query = $user->groups();
        if (isset($options['type'])) {
            $query->where('group_type_id', $options['type']);
        }
        if (isset($options['relation'])) {
            $query->wherePivot('relation', $options['relation']);
        }
        return new Paginator($query, $options);
    }

    public function attachGroup($subject, $subId, $groId, $data, $flags = 3)
    {
        $subj = $this->db->query('App:Subject')->findOrFail($subId);
        $grou = $this->db->query('App:Group', ['group_type'])
            ->findOrFail($groId);
        $grTy = $grou->group_type;
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateSubjectGroup', $grou
            );
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'relation' => [
                    'type' => 'string',
                    'enum' => array_keys($grTy->allowed_relations),
                ],
            ],
            'required' => ['relation'],
            'additionalProperties' => false,
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $v->assert($data);
        $changes = $subj->groups()->syncWithoutDetaching([
            $groId => $data,
        ]);
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'associateSubjectGroup',
                'object' => $subj,
            ]);
        }
        return count($changes['attached']);
    }

    public function detachGroup($subject, $subId, $groId, $flags = 3)
    {
        $subj = $this->db->query('App:Subject')->findOrFail($subId);
        $grou = $this->db->query('App:Group')->findOrFail($groId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateSubjectGroup', $subj
            );
        }
        $changes = $subj->groups()->detach($groId);
        return $changes > 0;
    }

    public function attachTerms($subject, $usrId, $data, $flags = 3)
    {
        $user = $this->db->query('App:Subject', ['person'])
            ->where('type', 'User')
            ->findOrFail($usrId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateUserTerm', $user
            );
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'terms' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'integer',
                        'minimum' => 1,
                    ],
                ],
            ],
            'additionalProperties' => false,
            'required' => ['terms'],
        ];
        $v = $this->validation->fromSchema($schema);
        $data = $this->validation->prepareData($schema, $data);
        $terms = $this->db->query('App:Term')
            ->whereIn('id', $data['terms'])
            ->get();
        $changes = $user->person->terms()->syncWithoutDetaching(
            $terms->pluck('id')->toArray()
        );
        foreach ($terms as $term) {
            if (in_array($term->id, $changes['attached'])) {
                $term->increment('count');
            }
        }
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'associateUserTerm',
                'object' => $user,
            ]);
        }
        return $changes['attached'];
    }

    public function detachTerm($subject, $usrId, $trmId, $flags = 3)
    {
        $user = $this->db->query('App:Subject', ['person'])
            ->where('type', 'User')
            ->findOrFail($usrId);
        $term = $this->db->query('App:Term')->findOrFail($trmId);
        if ($flags & Utils::AUTHFLAG) {
            $this->authorization->checkOrFail(
                $subject, 'associateUserTerm', $user
            );
        }
        $changes = $user->person->terms()->detach($trmId);
        if ($changes >= 1) {
            $term->decrement('count');
            return true;
        }
        return false;
    }
}
