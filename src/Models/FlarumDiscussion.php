<?php
namespace FlarumConnection\Models;

use FlarumConnection\Exceptions\InvalidUserException;

/**
 * Model class for Flarum discussions
 */
class FlarumDiscussion extends JsonApiModel
{
    /**
     * Title of the post
     *
     * @var string
     */
    public $title;

    /**
     * Content of the post
     *
     * @var string
     */
    public $content;

    /**
     * List of the tags associated
     *
     * @var array
     */
    public $tags;

    /**
     * Author of the discussion
     *
     * @var int
     */
    public $author;

    /**
     * Id of the discussion
     *
     * @var int
     */
    public $id;

    /**
     * Initialize the post
     *
     * @param string $title The title of the post
     * @param string $content The content of the post
     * @param array $tags Tags of the post
     * @param int|null $id
     */
    public function __construct(string $title,string $content,array $tags,?int $id = null){
        $this->title = $title;
        $this->content = $content;
        $this->tags = $tags;
        $this->id = $id;
    }

    /**
     * Create the Body for discussion creation
     *
     * @return array
     */
    public function getCreateDiscussionBody(): array
    {
        $tagsAdapted = [];
        foreach ( $this->tags as $tag){
            $tagsAdapted[] = [
                'type' => 'tags',
                'id' => "$tag"
            ];
        }
        return [
            'data' => [
                'type' => 'discussions',
                'attributes' => [
                    'title' => $this->title,
                    'content' => $this->content
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
     * @return array
     */
    public function getUpdateDiscussionBody(): array
    {
        $tagsAdapted = [];
        foreach ( $this->tags as $tag){
            $tagsAdapted[] = [
                'type' => 'tags',
                'id' => "$tag"
            ];
        }
        return [
            'data' => [
                'type' => 'discussions',
                'id' => "$this->id",
                'attributes' => [
                    'title' => $this->title,
                    'content' => $this->content
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
     * Extract a Flarum discussion object from json
     *
     * @param array $json The JSOn array
     * @return FlarumDiscussion     Flarum discussion
     * @throws InvalidUserException     Trigerred of the user is not well formated
     */
    public static function fromJSON(array $json):FlarumDiscussion{
        if (!self::validateRequiredFieldsFromDocument($json,['title','slug','commentsCount','participantsCount','startTime','readTime']))
        {
            throw new InvalidUserException('Invalid json input for a user model');
        }

        return new FlarumDiscussion(self::extractAttributeFromDocument($json,'title'), '',[],self::extractIdFromDocument($json));
    }

}
