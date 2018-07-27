<?php
namespace FlarumConnection\Models;


use FlarumConnection\Hydrators\AbstractHydrator;

use FlarumConnection\Hydrators\FlarumUsersHydrator;
use FlarumConnection\Serializers\AbstractSerializer;
use FlarumConnection\Serializers\FlarumUsersSerializer;

/**
 * Model class for Flarum user
 */
class FlarumUser extends AbstractModel
{
    /**
     * Id of the user
     *
     * @var int
     */
    public $userId;

    /**
     * Name of the user
     *
     * @var string
     */
    public $username;

    /**
     * Url of the avatar
     *
     * @var string
     */
    public $avatarUrl;

    /**
     * Biography of the user
     *
     * @var string
     */
    public $bio;

    /**
     * Date of join
     *
     * @var int
     */
    public $joinTime;

    /**
     * Last read time
     *
     * @var int
     */
    public $readTime;

    /**
     * Number of discussion created
     *
     * @var int
     */
    public $discussionsCount;

    /**
     * Number of comments realized
     *
     * @var int
     */
    public $commentsCount;

    /**
     * Email of the user
     *
     * @var string
     */
    public $email;

    /**
     * Last time connected on the forum
     *
     * @var int
     */
    public $lastSeenTime;

    /**
     * Number of news notifications
     *
     * @var int
     */
    public $newNotifications;

    /**
     * Number of unread notifications
     *
     * @var int
     */
    public $unreadNotifications;

    /**
     * Password of the user
     * @var string
     */
    public $password;

    /**
     * Indicate if the user is activated
     * @var bool
     */
    public $isActivated;

    /**
     * Get groups of the user
     * @var array
     */
    public $groups;

    /**
     * initialize a user object
     *
     * @param integer $userId         The id of the user
     * @param string  $username      The user name
     * @param string  $email         The email of the user
     */
    public function init(int $userId, string $username, string $email): void
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
    }

    /**
     * Initializ an object for a group update
     * @param integer $userId         The id of the user
     * @param string  $username       The user name
     * @param array $groups           The groups of the user
     */
    public function initGroup(int $userId, string $username,array $groups): void
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->groups = $groups;
    }

    /**
     * Add a new group to a user
     * @param FlarumGroup $group The group to add
     */
    public function addToGroup(FlarumGroup $group): void
    {
        if($this->groups === null){
            $this->groups = [];
        }
        $this->groups[] = $group;
    }

    /**
     * Remove a group from a user
     * @param int $id   The group to remove
     */
    public function removeFromGroup(int $id): void
    {
        foreach($this->groups as $key=>$group){
            if($group->groupId === $id){
                unset($this->groups[$key]);
            }
        }
    }

    /**
     * Return the name of the model
     * @return string
     */
    public function getModelName():string{
        return 'User';
    }

    /**
     * Retrieve the Serializer of the object
     * @return AbstractSerializer
     */
    public function getSerializer(): AbstractSerializer
    {
        return new FlarumUsersSerializer();
    }

    /**
     * Retrieve the hydrator of the object
     * @return AbstractHydrator
     */
    public function getHydrator(): AbstractHydrator
    {
        return new FlarumUsersHydrator();
    }





}
