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
    const GET_USER_PATH = '/api/users';

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
     * @param bool $admin
     * @return \Http\Promise\Promise
     * @throws InvalidUserException When no users are asspcoayed with the request
     */
    public function getUser(int $userId = null,bool $admin = false): \Http\Promise\Promise
    {
        return $this->getOne($this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, new FlarumUser(),$admin);
    }

    /**
     * Retrieve a user by user name
     * @param string $username  Name of the user to search for
     * @param bool $admin           Use admin mod
     * @return \Http\Promise\Promise    A FlarumUser
     */
    public function getUserByUserName(string $username, bool $admin = false): \Http\Promise\Promise{
        return $this->getAll($this->getUriSearchByUserName($username),new FlarumUser(),$admin)->then(
            function(iterable $array) use ($username){
                if($array===null || count($array) !== 1){
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
     * @return \Http\Promise\Promise
     * @throws InvalidUserException When the user is not
     */
    public function updateEmail(string $email, string $username, int $userId = null, bool $admin = false): \Http\Promise\Promise
    {
        return $this->updateEmailOrPassword($email, $username, '', false, $userId,$admin);
    }

    /**
     * Update the password of the user
     *
     * @param string $password The password of the user
     * @param string $username The login of the user
     * @param integer|null $userId The login of the user
     * @param bool $admin
     * @return \Http\Promise\Promise
     * @throws InvalidUserException
     */
    public function updatePassword(string $password, string $username, int $userId = null,bool $admin = false): \Http\Promise\Promise
    {
        return $this->updateEmailOrPassword('', $username, $password, true, $userId,$admin);
    }

    /**
     * Add a user to a group
     * @param int $userId               The id of the user
     * @param int $groupId              The id of the group
     * @param bool $admin               Do it in admin mod
     * @return \Http\Promise\Promise    A user
     */
    public function addToGroup(int $userId, int $groupId,bool $admin = false){
        return $this->getUser($userId,$admin)->then(
            function (FlarumUser $user) use ($groupId,$userId,$admin){
                $newGroup = new FlarumGroup();
                $newGroup->groupId = $groupId;
                $user->addToGroup($newGroup);
                $userUpdate = new FlarumUser();
                $userUpdate->initGroup($userId,$user->username,$user->groups);
                return $this->update($this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, $userUpdate, 200, $admin)->wait();
            },
            function(\Exception $e){
                return $e;
            }
        );
    }

    /**
     * Remove a user from a group
     * @param int $userId               The id of the user
     * @param int $groupId              The id of the group
     * @param bool $admin               Do it in admin mod
     * @return \Http\Promise\Promise    A user
     */
    public function removeFromGroup(int $userId, int $groupId,bool $admin = false){
        return $this->getUser($userId,$admin)->then(
            function (FlarumUser $user) use ($groupId,$userId,$admin){
                $user->removeFromGroup($groupId);
                $userUpdate = new FlarumUser();
                $userUpdate->initGroup($userId,$user->username,$user->groups);
                return $this->update($this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, $userUpdate, 200, $admin)->wait();

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
            true);

    }

    /**
     * Update the email or password of the user
     *
     * @param string $email The email of the user (can be empty)
     * @param string $username The username of the user
     * @param string $password The password of the user (can be empty)
     * @param boolean $updatePassword If true will update the password, otherwise will update the email
     * @param integer|null $userId Id of the user
     * @param bool $admin
     * @return \Http\Promise\Promise
     * @throws InvalidUserException When there is no user associated
     */
    private function updateEmailOrPassword(string $email, string $username, string $password, bool $updatePassword, int $userId = null, bool $admin = false): \Http\Promise\Promise
    {
        if ($userId === null) {
            $token = $this->getToken();
            if($token === false){
                throw new InvalidUserException('There is no currently defined user');
            }
            $userId = $this->token->userId;
        }
        if ($updatePassword) {
            $user = new FlarumUser();
            $user->username = $username;
            $user->userId = $userId;
            $user->password = $password;
        } else{
            $user = new FlarumUser();
            $user->username = $username;
            $user->userId = $userId;
            $user->email = $email;
        }
        return $this->update($this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, $user, 200, $admin);

    }

    /**
     * Return the uri to search by user name
     * @param string $userName  The name to search for
     * @return string   The uri to use
     */
    private function getUriSearchByUserName(string $userName){
        return $this->config->flarumUrl . self::GET_USER_PATH . '?'
            .urlencode('filter[q]').'='.urlencode($userName).
            '&'.urlencode('page[limit]').'=1';


    }

}
