<?php
namespace FlarumConnection\Features;

use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumToken;
use FlarumConnection\Models\FlarumUser;
use FlarumConnection\Exceptions\InvalidLoginException;
use  FlarumConnection\Exceptions\InvalidUserCreationException;
use \GuzzleHttp\Psr7\Request;
use \Psr\Log\LoggerInterface;

/**
 * Handle SSO features
 */
class FlarumSSO extends AbstractFeature{

    /**
     * Path for Get token
     */
    const GET_TOKEN_PATH = '/api/token';

    /**
     * Path to get notifications
     */
    const GET_NOTIFICATIONS_PATH = '/api/notifications';

        /**
     * Path to get notifications
     */
    const CREATE_USER_PATH = '/api/users';

    /**
     * Name of the flarum cookie
     */
    const FLARUM_COOKIE = 'flarum_remember';


    /**
     * Initialize the feature with the config
     *
     * @param FlarumConnectorConfig $config     Configuration for flarum connector
     * @param LoggerInterface $logger           Logger interface
     */
    public function __construct(FlarumConnectorConfig $config,LoggerInterface $logger){
        $this->init($config,$logger);
    }

    /**
     * Realize a login operation over Flarum
     *
     * @param string $login                             Login of the user
     * @param string $password                          Password of the user
     * @return \GuzzleHttp\Promise\promiseinterface     Promise of a token
     */
    public function login(string $login,string $password):\GuzzleHttp\Promise\promiseinterface { 
 
        $body = json_encode([
            'identification' => $login,
            'password' => $password
        ]);
        
        $headers = [
            'Content-Type:' =>'application/json',
            'Content-Length' =>  strlen($body)
        ];

        $request = new Request('POST', $this->config->flarumUrl. self::GET_TOKEN_PATH, $headers, $body);
        $promise = $this->http->sendAsync($request);
        global $login;
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res) use ($login) {
                if($res->getStatusCode() === 200){
                    try{
                        return FlarumToken::fromToken(json_decode($res->getBody()));
                    } catch(\Exception $e){       
                        $this->logger->debug('Exception triggered on login :'.$e->getMessage());
                        return $e;
                    }
                } 
                $this->logger->debug('Invalid password for '.$login.' '.$res->getStatusCode().' returned');
                return new InvalidLoginException('Invalid password for '.$login.' '.$res->getStatusCode().' returned');               

            },
            function (\Exception $e) {
                return $e;
            }
        );

    }

    /**
     * Create a new user
     *
     * @param string $login         The login of the user
     * @param string $password      The password of the user to be created
     * @param string $email         The email to be sent
     * @return \GuzzleHttp\Promise\promiseinterface     Promise of a user
     */
    public function signup(string $login,string $password,string $email):\GuzzleHttp\Promise\promiseinterface {
        $body = json_encode([
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'username' => $login,
                    'password' => $password,
                    'email' => $email,
                    'isActivated' => true
                ]
            ]
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Length'=> strlen($body),
            'Authorization' => 'Token ' . $this->config->flarumAPIKey. '; userId=1'
        ];

     
    
        $request = new Request('POST', $this->config->flarumUrl.self::CREATE_USER_PATH, $headers, $body);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response $res) {
                if($res->getStatusCode() === 201){
                    try{
                        $content = json_decode($res->getBody(),true);    
                        return FlarumUser::fromJSON($content);                   
                    } catch(\Exception $e){       
                        return new InvalidUserCreationException($e->getMessage());
                    }
                } 

                $this->logger->debug('Invalid user creation '.$res->getStatusCode().' returned');
                return new InvalidUserCreationException('Invalid user creation '.$res->getStatusCode().' returned');         

            },
            function (\Exception $e) {
                $this->logger->debug('Exception trigerred on user creation'.$e->getMessage());
                    return new InvalidUserCreationException($e->getMessage());
            }
        );
    }




    /**
     * Check if a user is connected
     *
     * @param  string $cookieSession Value of the cookie
     * @return \GuzzleHttp\Promise\promiseinterface     Promise of a boolean
     */
    public function isUserConnected(string $cookieSession):\GuzzleHttp\Promise\promiseinterface{     
        $headers = [
            'Content-Type:' =>'application/json',
            'Authorization' => 'Token ' . $cookieSession. ';'
        ];

        $request = new Request('GET', $this->config->flarumUrl. self::GET_NOTIFICATIONS_PATH, $headers);
        $promise = $this->http->sendAsync($request);
        return $promise->then(
            function (\GuzzleHttp\Psr7\Response  $res) {
               return $res->getStatusCode() === 200;
            },function ( $e) {  
                $this->logger->debug('Exception trigerred on is user connected'.$e->getMessage());          
                return $e;
            });

    }



}
