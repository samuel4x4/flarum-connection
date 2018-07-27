<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 14:55
 */

namespace FlarumConnection\Serializers;
use FlarumConnection\Models\FlarumPost;


/**
 * Serializer for Post
 * Class FlarumPostsSerializer
 * @package FlarumConnection\Serializers
 */
class FlarumPostsSerializer extends AbstractSerializer
{
    /**
     * Get the body for the creation of a post
     * @param mixed $post     The object to serialize
     * @return array    The serialized body
     */
    public function getBodyInsert($post): array
    {
        if($post instanceof FlarumPost){
            $ret =  [
                'data' => [
                    'type' => 'posts',
                    'attributes' => [
                        'content' => $post->content
                    ],
                    'relationships' =>[
                        'discussion' => [
                            'data' =>
                            [
                                'type' => 'discussion',
                                'id' => $post->discussion->id
                            ]
                        ]

                    ]

                ]
            ];

            return $ret;
        }
        echo 'ko';
        return [];
    }

    /**
     * Get the body for the update of a post
     * @param mixed $post     The object to serialize
     * @return array          The serialized ovject
     */
    public function getBodyUpdate($post): array
    {
        if($post instanceof FlarumPost) {
            return [
                'data' => [
                    'type' => 'posts',
                    'attributes' => [
                        'content' => $post->content
                    ],


                ]
            ];
        }
        return [];
    }
}