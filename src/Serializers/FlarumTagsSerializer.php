<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 14:55
 */

namespace FlarumConnection\Serializers;
use FlarumConnection\Models\FlarumTag;


/**
 * Serializer for tags
 * Class FlarumTagsSerializer
 * @package FlarumConnection\Serializers
 */
class FlarumTagsSerializer extends AbstractSerializer
{
    /**
     * Get the body for the creation of a tag
     * @param mixed $tag      The object to serialize
     * @return array    The serialized body
     */
    public function getBodyInsert($tag): array
    {
        if($tag instanceof FlarumTag){
            return [
                'data' => [
                    'type' => 'tags',
                    'attributes' => [
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                        'description' => $tag->description,
                        'color' => $tag->color,
                        'isHidden' => $tag->isHidden,
                        'isRestricted' => $tag->isRestricted

                    ]

                ]
            ];
        }
        return [];
    }

    /**
     * Get the body for the update of a tag
     * @param mixed $tag      The object to serialize
     * @return array    The serialized body
     */
    public function getBodyUpdate($tag): array
    {
        if($tag instanceof FlarumTag) {
            return [
                'data' => [
                    'type' => 'tags',
                    'attributes' => [
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                        'description' => $tag->description,
                        'color' => $tag->color,
                        'isHidden' => $tag->isHidden,
                        'isRestricted' => $tag->isRestricted

                    ]

                ]
            ];
        }
        return [];
    }

    /**
     * Get the body to set permissions
     * @param int $idTag The if of the tag
     * @param array $groups The groups to add
     * @param string $permission The permission to set
     * @return array        The json body as array
     */
    public function getBodyPermission(int $idTag,array $groups, string $permission): array
    {
        return [
            'groupIds' => $groups,
            'permission' => 'tag'.$idTag.'.'.$permission
        ];
    }

    public function getBodyRestricted($tagId,$restricted): array
    {
        return [
            'data' => [
                'type' => 'tags',
                'id' => $tagId,
                'attributes' => [
                    'isRestricted' =>$restricted
                ]

            ]
        ];
    }
}