<?php

namespace App\Resource;

use App\Mail\SignUpEmail;
use App\Mail\PasswordResetEmail;
use App\Util\Exception\AppException;
use App\Util\Exception\UnauthorizedException;
use App\Util\Utils;
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
        return $this->db->query('App:Subject')
            ->where('type', 'User')
            ->findOrFail($id);
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
                $subject, 'updatePassword', $user
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
        $token->delete();
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
        $role = $this->db->query('App:Term')->findOrFail($rolId);
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
            $role => ['expires_on' => $exp],
        ]);
        if ($flags & Utils::LOGFLAG) {
            $this->resources['log']->createLog($subject, [
                'action' => 'associateSubjectRole',
                'object' => $subj,
            ]);
        }
        return count($changes['attached']);
    }
}
