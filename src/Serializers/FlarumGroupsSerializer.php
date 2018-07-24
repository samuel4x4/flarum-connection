<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 14:55
 */

namespace FlarumConnection\Serializers;

use FlarumConnection\Models\FlarumGroup;


/**
 * Serializer for tags
 * Class FlarumTagsSerializer
 * @package FlarumConnection\Serializers
 */
class FlarumGroupsSerializer extends AbstractSerializer
{
    /**
     * Get the body for the creation of a group
     * @return array
     */
    public function getBodyInsert($group): array
    {
        if ($group instanceof FlarumGroup) {
            return [
                'data' => [
                    'type' => 'tags',
                    'attributes' => [
                        'nameSingular' => $group->nameSingular,
                        'namePlural' => $group->namePlural,
                        'color' => $group->color,
                        'icon' => $group->icon

                    ]

                ]
            ];
        }
        return [];

    }

    /**
     * Get the body for the update of a group
     * @return array
     */
    public function getBodyUpdate($group): array
    {
        if ($group instanceof FlarumGroup) {
            return [
                'data' => [
                    'type' => 'tags',
                    'id' => $group->groupId,
                    'attributes' => [
                        'nameSingular' => $group->nameSingular,
                        'namePlural' => $group->namePlural,
                        'color' => $group->color,
                        'icon' => $group->icon

                    ]

                ]
            ];
        }
        return [];
    }
}