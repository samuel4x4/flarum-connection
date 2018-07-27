<?php
namespace FlarumConnection\Features;

use FlarumConnection\Exceptions\InvalidObjectException;
use \FlarumConnection\Exceptions\InvalidUserException;

use \FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumGroup;
use \FlarumConnection\Models\FlarumUser;

use Http\Promise\RejectedPromise;
use \Psr\Log\LoggerInterface;


/**
 * Handle user management on Flarum
 */
class FlarumUserManagement extends AbstractFeature
{

    /**
     * Path for Get User
     */
     public const GET_USER_PATH = '/api/users';

    /**
     * Initialize the feature with the config
     *
     * @param FlarumConnectorConfig $config     Configuration for flarum connector
     * @param LoggerInterface $logger           Logger interface
     */
    public function __construct(FlarumConnectorConfig $config, LoggerInterface $logger)
    {
        $this->init($config, $logger);
    }

    /**
     * Get the  user
     * @param int|null $userId The id of the user of null if the objective is to get the current user
     * @param int|null $user
     * @return \Http\Promise\Promise
     */
    public function getUser(int $userId = null,?int $user = null): \Http\Promise\Promise
    {
        return $this->getOne($this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, new FlarumUser(),$user);
    }

    /**
     * Retrieve a user by user name
     * @param string $username Name of the user to search for
     * @param int|null $user
     * @return \Http\Promise\Promise    A FlarumUser
     */
    public function getUserByUserName(string $username, ?int $user = null): \Http\Promise\Promise{
        return $this->getAll($this->getUriSearchByUserName($username),new FlarumUser(),$user)->then(
            function(array $array) use ($username){
                if($array===null || \count($array) !== 1){
                    return new RejectedPromise(new InvalidObjectException('User '.$username.'not found'));
                }
                return $array[0];
            }
        );
    }

    /**
     * Update the email of the user
     *
     * @param string $email The email of the user
     * @param string $username The login of the user
     * @param integer|null $userId The id of the user
     * @param int|null $user    The user to call the API
     * @return \Http\Promise\Promise    A user
     */
    public function updateEmail(string $email, string $username, int $userId = null, ?int $user = null): \Http\Promise\Promise
    {
        return $this->updateEmailOrPassword($email, $username, '', false, $userId,$user);
    }

    /**
     * Update the password of the user
     *
     * @param string $password The password of the user
     * @param string $username The login of the user
     * @param integer|null $userId The login of the user
     * @param int|null $user    The user to call the WS
     * @return \Http\Promise\Promise    A user promise
     */
    public function updatePassword(string $password, string $username, int $userId = null,?int $user = null): \Http\Promise\Promise
    {
        return $this->updateEmailOrPassword('', $username, $password, true, $userId,$user);
    }

    /**
     * Add a user to a group
     * @param int $userId The id of the user
     * @param int $groupId The id of the group
     * @param int|null $user
     * @return \Http\Promise\Promise    A user
     */
    public function addToGroup(int $userId, int $groupId,?int $user = null): \Http\Promise\Promise
    {
        return $this->getUser($userId,$user)->then(
            function (FlarumUser $fUser) use ($groupId,$userId,$user){
                $newGroup = new FlarumGroup();
                $newGroup->groupId = $groupId;
                $fUser->addToGroup($newGroup);
                $userUpdate = new FlarumUser();
                $userUpdate->initGroup($userId,$fUser->username,$fUser->groups);
                return $this->update($this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, $userUpdate, 200, $user)->wait();
            },
            function(\Exception $e){
                return $e;
            }
        );
    }

    /**
     * Remove a user from a group
     * @param int $userId The id of the user
     * @param int $groupId The id of the group
     * @param int|null $user    The user id for the call of the ws
     * @return \Http\Promise\Promise    A promise of a boolean
     */
    public function removeFromGroup(int $userId, int $groupId,?int $user = null): \Http\Promise\Promise
    {
        return $this->getUser($userId,$user)->then(
            function (FlarumUser $flUser) use ($groupId,$userId,$user){
                $flUser->removeFromGroup($groupId);
                $userUpdate = new FlarumUser();
                $userUpdate->initGroup($userId,$flUser->username,$flUser->groups);
                return $this->update($this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, $userUpdate, 200, $user)->wait();

            },
            function(\Exception $e){
                return $e;
            }
        );
    }

    /**
     * Delete an existing user
     *
     * @param int $userId The id of the user to delete
     * @return \Http\Promise\Promise
     * @throws InvalidUserException
     */
    public function deleteUser(int $userId): \Http\Promise\Promise{
        //Delete a user is an admin only feature
        return $this->delete(
            $this->config->flarumUrl . self::GET_USER_PATH .'/'.$userId,
            new FlarumUser(),
            204,
            null);

    }

    /**
     * Update the email or password of the user
     *
     * @param string $email The email of the user (can be empty)
     * @param string $username The username of the user
     * @param string $password The password of the user (can be empty)
     * @param boolean $updatePassword If true will update the password, otherwise will update the email
     * @param integer|null $userId Id of the user
     * @param int|null $user        The user to call the ws
     * @return \Http\Promise\Promise    A promise of a user
     */
    private function updateEmailOrPassword(string $email, string $username, string $password, bool $updatePassword, int $userId = null, ?int $user = null): \Http\Promise\Promise
    {

        if ($updatePassword) {
            $flUser = new FlarumUser();
            $flUser->username = $username;
            $flUser->userId = $userId;
            $flUser->password = $password;
        } else{
            $flUser = new FlarumUser();
            $flUser->username = $username;
            $flUser->userId = $userId;
            $flUser->email = $email;
        }
        return $this->update($this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, $flUser, 200, $user);

    }

    /**
     * Return the uri to search by user name
     * @param string $userName  The name to search for
     * @return string   The uri to use
     */
    private function getUriSearchByUserName(string $userName): string
    {
        return $this->config->flarumUrl . self::GET_USER_PATH . '?'
            .urlencode('filter[q]').'='.urlencode($userName).
            '&'.urlencode('page[limit]').'=1';


    }

}
