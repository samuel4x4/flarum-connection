<?php
namespace FlarumConnection\Models;

use FlarumConnection\Exceptions\InvalidUserException;

/**
 * Model class for Flarum user
 */
class FlarumUser extends JsonApiModel
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
     * initialize a user object
     *
     * @param integer $userId         The id of the user
     * @param string  $username      The user name
     * @param string  $email         The email of the user
     */
    public function __construct(int $userId, string $username, string $email)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
    }

    /**
     * Create a user from JSON input
     *
     * @param array $json Output from json parsing
     * @return FlarumUser               The created user
     * @throws InvalidUserException     If their is no user associated
     */
    public static function fromJSON(array $json): FlarumUser
    {
        if (!self::validateRequiredFieldsFromDocument($json,['username','email']))
        {
            throw new InvalidUserException('Invalid json input for a user model');
        }
        $user = new FlarumUser(self::extractIdFromDocument($json), self::extractAttributeFromDocument($json,'username'), self::extractAttributeFromDocument($json,'email'));
        $user->avatarUrl = self::extractAttributeFromDocument($json,'avatarUrl');
        $user->bio = self::extractAttributeFromDocument($json,'bio');
        $user->joinTime = self::parseDate(self::extractAttributeFromDocument($json,'joinTime'));
        $user->commentsCount = self::extractAttributeFromDocument($json,'commentsCount');
        $user->discussionsCount = self::extractAttributeFromDocument($json,'discussionsCount');
        $user->lastSeenTime = self::parseDate(self::extractAttributeFromDocument($json,'lastSeenTime'));
        //$user->readTime = self::parseDate(self::extractAttribute($json,'readTime'));
        $user->unreadNotifications = self::extractAttributeFromDocument($json,'unreadNotificationsCount');
        $user->newNotifications = self::extractAttributeFromDocument($json,'newNotificationsCount');
        return $user;
   
    }

    /**
     * Get the password update data
     * @param string $password    The password to change
     * @return array    The array to be transformed to JSON
     */
    public function getPasswordUpdateBody(string $password): array
    {
        return [
            'data' => [
                'type' => 'users',
                'id' => $this->userId,
                'attributes' => [
                    'username' => $this->username,
                    'password' => $password,
                ],
            ],
        ];

    }

    /**
     * Get the email update data
     * @param string    $email    The email to change
     * @return array    The array to be transformed to JSON
     */
    public function getEmailUpdateBody(string $email): array
    {
        return [
            'data' => [
                'type' => 'users',
                'id' => $this->userId,
                'attributes' => [
                    'username' => $this->username,
                    'email' => $email,
                ],
            ],
        ];

    }



}
