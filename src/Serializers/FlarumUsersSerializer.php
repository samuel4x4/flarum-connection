<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 14:56
 */

namespace FlarumConnection\Serializers;


use FlarumConnection\Models\FlarumUser;

/**
 * Serialize user in JSON-API format
 * Class FlarumUsersSerializer
 * @package FlarumConnection\Serializers
 */
class FlarumUsersSerializer extends AbstractSerializer
{

    /**
     * Provide the json body for an update
     * @param \stdClass $instance     The instance of the model
     * @return array        The body as array
     */
    public function getBodyUpdate($instance): array
    {
        if($instance instanceof FlarumUser){
            if($instance->groups !== null){
                return $this->getBodyAddToGroup($instance);
            }
            else if ($instance->password !== null) {
                return [
                    'data' => [
                        'type' => 'users',
                        'id' => $instance->userId,
                        'attributes' => [
                            'username' => $instance->username,
                            'password' => $instance->password,
                        ],
                    ],
                ];
            } else if ($instance->email !== null) {
                return [
                    'data' => [
                        'type' => 'users',
                        'id' => $instance->userId,
                        'attributes' => [
                            'username' => $instance->username,
                            'email' => $instance->email
                        ],
                    ],
                ];
            }
            return [
                'data' => [
                    'type' => 'users',
                    'id' => $instance->userId,
                    'attributes' => [
                        'username' => $instance->username
                    ],
                ],
            ];
        }
        return [];


    }

    /**
     * Get body to add to a group
     * @param $instance The flarum user instance
     * @return array    The body
     */
    public function getBodyAddToGroup($instance){
        if($instance instanceof FlarumUser) {
            $groups = [];
            foreach ($instance->groups as $group){
                $groups[] = [
                    'type' => 'groups',
                    'id' => $group->groupId
                ];
            }
                return [
                    'data' => [
                        'type' => 'users',
                        'id' => $instance->userId,
                        'attributes' => [
                            'username' => $instance->username
                        ],
                        'relationships' => [
                            'groups' =>[
                                'data' => $groups
                            ]
                        ]
                    ]
                ];
        }
        return [];
    }

    /**
     * Provide the json body for an insert
     * @param \stdClass $instance     The instance of the model
     * @return array        The body as array
     */
    public function getBodyInsert($instance): array
    {
        if($instance instanceof FlarumUser) {
            return [
                'data' => [
                    'type' => 'users',
                    'attributes' => [
                        'username' => $instance->username,
                        'password' => $instance->password,
                        'email' => $instance->email,
                        'isActivated' => true
                    ]
                ]
            ];
        }
        return [];
    }



}