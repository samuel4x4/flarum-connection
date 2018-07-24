<?php
namespace FlarumConnection\Models;
use FlarumConnection\Exceptions\InvalidTagException;
use FlarumConnection\Hydrators\AbstractHydrator;
use FlarumConnection\Hydrators\FlarumPostsHydrator;

use FlarumConnection\Serializers\AbstractSerializer;
use FlarumConnection\Serializers\FlarumPostsSerializer;


/**
 * Model class for Flarum tags
 */
class FlarumPost extends AbstractModel
{

    /**
     * Name of the tag
     *
     * @var int
     */
    public $number;

    /**
     * Slug of the tag
     *
     * @var int
     */
    public $time;

    /**
     * Content type of the post (could be comment,discussionRenamed)
     *
     * @var string
     */
    public $contentType;


    /**
     * Content of the post in html
     *
     * @var string
     */
    public $contentHtml;

    /**
     * Content of the post stripped
     *
     * @var string
     */
    public $content;

    /**
     * Ip address  of the user that have posted
     *
     * @var string
     */
    public $ipAddress;

    /**
     * Does the current user can edit
     *
     * @var bool
     */
    public $canEdit;

    /**
     * Does the current user can delete
     * @var bool
     */
    public $canDelete;

    /**
     * Is the comment approved
     * @var bool
     */
    public $isApproved;

    /**
     * Can the current user approve the post
     * @var bool
     */
    public $canApprove;

    /**
     * Does the current user can flag
     * @var bool
     */
    public $canFlag;

    /**
     * Does the current user can delete
     * @var bool
     */
    public $canlike;


    /**
     * User associated with the post
     * @var FlarumUser
     */
    public $user;


    /**
     * The id of the tag
     * @var int
     */
    public $postId;


    /**
     * Id of the discussion associated
     * @var FlarumDiscussion
     */
    public $discussion;


    /**
     * Initialize the post
     * @param string $content       The html post
     * @param int|null $id The id of the tag
     */
    public function init(string $content,  ?int $id = null){
        $this->content = $content;
        $this->postId = $id;
    }

    /**
     * Return the name of the model
     * @return string
     */
    public function getModelName():string{
        return 'Post';
    }

    /**
     * Retrieve the Serializer of the object
     * @return AbstractSerializer
     */
    public function getSerializer(): AbstractSerializer
    {
        return new FlarumPostsSerializer();
    }

    /**
     * Retrieve the hydrator of the object
     * @return AbstractHydrator
     */
    public function getHydrator(): AbstractHydrator
    {
        return new FlarumPostsHydrator();
    }






}
