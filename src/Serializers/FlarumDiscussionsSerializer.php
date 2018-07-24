<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 14:23
 */

namespace FlarumConnection\Serializers;
use FlarumConnection\Models\FlarumDiscussion;


/**
 * Serialize Flarum Discussions in order to make requests
 * Class FlarumDiscussionsSerializer
 * @package FlarumConnection\Serializers
 */
class FlarumDiscussionsSerializer extends AbstractSerializer
{
    /**
     * Create the Body for discussion creation
     *
     * @param FlarumDiscussion $discussion  The discussion to serialize
     * @return array
     */
    public function getBodyInsert($discussion): array
    {
        $tagsAdapted = [];
        foreach ( $discussion->tags as $tag){
            $tagsAdapted[] = [
                'type' => 'tags',
                'id' => "$tag"
            ];
        }
        return [
            'data' => [
                'type' => 'discussions',
                'attributes' => [
                    'title' => $discussion->title,
                    'content' => $discussion->content
                ],
                'relationships' => [
                    'tags' => [
                        'data' => $tagsAdapted
                    ]
                ]
            ]
        ];

    }

    /**
     * Create the Body for discussion update
     *
     * @param FlarumDiscussion $discussion
     * @return array
     */
    public function getBodyUpdate($discussion): array
    {
        $tagsAdapted = [];
        foreach ( $discussion->tags as $tag){
            $tagsAdapted[] = [
                'type' => 'tags',
                'id' => "$tag"
            ];
        }
        return [
            'data' => [
                'type' => 'discussions',
                'id' => "$discussion->id",
                'attributes' => [
                    'title' => $discussion->title,
                    'content' => $discussion->content
                ],
                'relationships' => [
                    'tags' => [
                        'data' => $tagsAdapted
                    ]
                ]
            ]
        ];

    }


}