<?php
namespace FlarumConnection\Models;


use FlarumConnection\Hydrators\AbstractHydrator;
use FlarumConnection\Hydrators\FlarumDiscussionHydrator;
use FlarumConnection\Serializers\AbstractSerializer;
use FlarumConnection\Serializers\FlarumDiscussionsSerializer;

/**
 * Model class for Flarum discussions
 */
class FlarumDiscussion extends AbstractModel
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
     * Start date of the discussion
     * @var int
     */
    public $createdAt;

    /**
     * Last post date
     * @var int
     */
    public $lastPostedAt;

    /**
     * Last read date
     * @var int
     */
    public $lastReadAt;

    /**
     * Number of participants
     * @var int
     */
    public $participantsCount;

    /**
     * Number of comments
     * @var int
     */
    public $commentsCount;

    /**
     * Slug
     * @var string
     */
    public $slug;

    /**
     * Number of the last post
     * @var int
     */
    public $lastPostNumber;

    /**
     * Number of the last read post
     * @var int
     */
    public $lastReadPostNumber;

    /**
     * First user to post
     * @var FlarumUser
     */
    public $user;

    /**
     * Last user to post
     * @var FlarumUser
     */
    public $lastPostedUser;


    /**
     * Initialize the post
     *
     * @param string $title The title of the post
     * @param string $content The content of the post
     * @param array $tags Tags of the post
     * @param int|null $id
     */
    public function init(string $title,string $content,array $tags,?int $id = null): void
    {
        $this->title = $title;
        $this->content = $content;
        $this->tags = $tags;
        $this->id = $id;
    }



    /**
     * Return the name of the model
     * @return string
     */
    public function getModelName():string{
        return 'Discussion';
    }


    /**
     * Retrieve the Serializer of the object
     * @return AbstractSerializer
     */
    public function getSerializer(): AbstractSerializer
    {
        return new FlarumDiscussionsSerializer();
    }

    /**
     * Retrieve the hydrator of the object
     * @return AbstractHydrator
     */
    public function getHydrator(): AbstractHydrator
    {
        return new FlarumDiscussionHydrator();
    }
}
