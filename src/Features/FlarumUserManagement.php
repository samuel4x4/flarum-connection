<?php
namespace FlarumConnection\Features;

use \FlarumConnection\Exceptions\InvalidUserException;
use \FlarumConnection\Exceptions\InvalidUserUpdateException;
use \FlarumConnection\Models\FlarumConnectorConfig;
use \FlarumConnection\Models\FlarumUser;
use \GuzzleHttp\Psr7\Request;
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
     * @return \GuzzleHttp\Promise\promiseinterface The result of get user
     * @throws InvalidUserException When no users are asspcoayed with the request
     */
    public function getUser(?int $userId = null): \GuzzleHttp\Promise\promiseinterface
    {
        if ($userId === null) {
            $token = $this->getToken();
            if($token === false){
                throw new InvalidUserException('There is no currently defined user');
            }
            $userId = $this->token->userId;
        }
        $headers = [
            'Content-Type:' => 'application/json',
            'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId=1',

        ];
        $request = new Request('GET', $this->config->flarumUrl . self::GET_USER_PATH . '/' . $userId, $headers);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res) {
                if ($res->getStatusCode() === 200) {
                    try {
                        $content = json_decode($res->getBody(),true);
                        return FlarumUser::fromJSON($content);
                    } catch (\Exception $e) {
                        return new InvalidUserException($e->getMessage());
                    }
                }
                $this->logger->debug('Invalid user retrieval ' . $res->getStatusCode() . ' returned');
                return new InvalidUserException('Error during user retrieval');

            },
            function (\Exception $e) {
                $this->logger->debug('Exception trigerred on user get' . $e->getMessage());
                return new InvalidUserException('Error during user retrieval');
            }
        );

    }

    /**
     * Update the email of the user
     *
     * @param string $email The email of the user
     * @param string $username The login of the user
     * @param integer|null $userId The id of the user
     * @return \GuzzleHttp\Promise\promiseinterface     A promise of an exception or of a user
     * @throws InvalidUserException When the user is not
     */
    public function updateEmail(string $email, string $username, int $userId = null): \GuzzleHttp\Promise\promiseinterface
    {
        return $this->updateEmailOrPassword($email, $username, '', false, $userId);
    }

    /**
     * Update the password of the user
     *
     * @param string $password The password of the user
     * @param string $username The login of the user
     * @param integer|null $userId The login of the user
     * @return \GuzzleHttp\Promise\promiseinterface     A promise of an exception or of a user
     * @throws InvalidUserException
     */
    public function updatePassword(string $password, string $username, int $userId = null): \GuzzleHttp\Promise\promiseinterface
    {
        return $this->updateEmailOrPassword('', $username, $password, true, $userId);
    }

    /**
     * Delete an existing user
     *
     * @param int $userId The id of the user to delete
     * @return \GuzzleHttp\Promise\promiseinterface     A promise of an exception or true
     */
    public function deleteUser(int $userId): \GuzzleHttp\Promise\promiseinterface
    {
        $headers = [
            'Content-Type:' => 'application/json',
            'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId=1',
        ];

        $request = new Request('DELETE', $this->config->flarumUrl . self::GET_USER_PATH .'/'.$userId, $headers);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res) {
                if ($res->getStatusCode() === 204) {
                    return true;
                }
                $this->logger->debug('Invalid user deletion ' . $res->getStatusCode() . ' returned');
                return new InvalidUserUpdateException('Invalid user deletion ' . $res->getStatusCode() . ' returned');

            },
            function (\Exception $e) {
                $this->logger->debug('Exception trigerred on user delete' . $e->getMessage());
                return new InvalidUserUpdateException('Exception trigerred on user delete');
            }
        );

    }

    /**
     * Update the email or password of the user
     *
     * @param string $email The email of the user (can be empty)
     * @param string $username The username of the user
     * @param string $password The password of the user (can be empty)
     * @param boolean $updatePassword If true will update the password, otherwise will update the email
     * @param integer|null $userId Id of the user
     * @return \GuzzleHttp\Promise\promiseinterface     A promise of an exception or of a user
     * @throws InvalidUserException           When there is no user associated
     */
    private function updateEmailOrPassword(string $email, string $username, string $password, bool $updatePassword, int $userId = null): \GuzzleHttp\Promise\promiseinterface
    {
        if ($userId === null) {
            $token = $this->getToken();
            if($token === false){
                throw new InvalidUserException('There is no currently defined user');
            }
            $userId = $this->token->userId;
        }
        $newUser = new FlarumUser($userId, $username, '');

        if ($updatePassword) {
            $body = json_encode($newUser->getPasswordUpdateBody($password));
        } else {
            $body = json_encode($newUser->getEmailUpdateBody($email));
        }

        $headers = [
            'Content-Type:' => 'application/json',
            'Authorization' => 'Token ' . $this->config->flarumAPIKey . '; userId=1',
            'Content-Length' => strlen($body),
        ];
        $request = new Request('PATCH', $this->config->flarumUrl . FlarumSSO::CREATE_USER_PATH.'/'.$userId, $headers, $body);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res) {
                if ($res->getStatusCode() === 200) {
                    try {
                        $content = json_decode($res->getBody(),true);
                        return FlarumUser::fromJSON($content);
                    } catch (\Exception $e) {
                        var_dump($e->getMessage());
                        return new InvalidUserUpdateException($e->getMessage());
                    }
                }
                $content = json_decode($res->getBody());
                var_dump($content);
                $this->logger->debug('Invalid user update ' . $res->getStatusCode() . ' returned');
                return new InvalidUserUpdateException('Invalid user update ' . $res->getStatusCode() . ' returned');

            },
            function (\Exception $e) {
                var_dump($e->getMessage());
                $this->logger->debug('Exception trigerred on user update' . $e->getMessage());
                return new InvalidUserUpdateException('Exception trigerred on user update');
            }
        );
    }

}
