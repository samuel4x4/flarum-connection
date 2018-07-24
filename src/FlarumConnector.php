<?php
namespace FlarumConnection;

require '../vendor/autoload.php';

use FlarumConnection\Features\FlarumDiscussionsManager;
use FlarumConnection\Features\FlarumGroupsManager;
use FlarumConnection\Features\FlarumPostsManager;
use FlarumConnection\Features\FlarumTagsManager;

use Psr\Log\LoggerInterface;

use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumToken;
use FlarumConnection\Features\FlarumSSO;
use FlarumConnection\Features\FlarumUserManagement;

/**
 * Connector class for Flarum
 */
class FlarumConnector{

    /**
     * Name of the flarum cookie
     */
    const FLARUM_COOKIE = 'flarum_remember';

    /**
     * Configuration for Flarum
     *
     * @var FlarumConnectorConfig
     */
    private $config;

    /**
     * Logger (PSR3 interface)
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Flarum SSO feature
     *
     * @var FlarumSSO
     */
    private $flarumSSO;

    /**
     * Flarum User management feature
     * @var FlarumUserManagement
     */
    private $flarumUserManagement;

    /**
     * IFeatureListenner listenners
     *
     * @var array
     */
    private $listenners;

    /**
     * Flarum discussion feature
     * @var FlarumDiscussionsManager
     */
    private $flarumDiscussionManagement;

    /**
     * Handle tags
     * @var FlarumTagsManager
     */
    private $flarumTagManagement;

    /**
     * Handle groups
     * @var FlarumGroupsManager
     */
    private $flarumGroupManagement;

    /**
     * Handle posts
     * @var FlarumPostsManager
     */
    private $flarumPostManagement;

    /**
     * Initialize the service
     *
     * @param FlarumConnectorConfig $config  The configuration for the service
     * @param LoggerInterface       $logger  PSR-3 Logger interface
     * @param FlarumToken|null      $token   The user (if the user is connected)
     */
    public function __construct(FlarumConnectorConfig $config, LoggerInterface $logger, ?FlarumToken $token = null){
        $this->config = $config;
        $this->logger = $logger;
        $this->flarumSSO = new FlarumSSO( $config,  $logger);
        $this->flarumUserManagement = new FlarumUserManagement($config,  $logger);
        $this->flarumDiscussionManagement = new FlarumDiscussionsManager($config,$logger);
        $this->flarumTagManagement = new FlarumTagsManager($config,$logger);
        $this->flarumGroupManagement = new FlarumGroupsManager($config,$logger);
        $this->flarumPostManagement = new FlarumPostsManager($config,$logger);
        $this->listenners = [$this->flarumSSO, $this->flarumUserManagement, $this->flarumDiscussionManagement,$this->flarumTagManagement,$this->flarumGroupManagement,$this->flarumPostManagement];
        if($token !== null){
            $this->setToken($token);
        }
    }

    /**
     * Retrieve the SSO feature
     *
     * @return FlarumSSO    The initialized SSO feature
     */
    public function getSSO():FlarumSSO{
        return $this->flarumSSO;
    }

    /**
     * Retrieve the User Management feature
     *
     * @return FlarumUserManagement   The user management feature
     */
    public function getUserManagement():FlarumUserManagement{
        return $this->flarumUserManagement;
    }

    /**
     * Get the discussion management feature
     * @return FlarumDiscussionsManager  The discussion management feature
     */
    public function getDiscussionManagement():FlarumDiscussionsManager{
        return $this->flarumDiscussionManagement;
    }

    /**
     * Get the tags feature
     * @return FlarumTagsManager  The discussion management feature
     */
    public function getTagsManagement():FlarumTagsManager{
        return $this->flarumTagManagement;
    }

    /**
     * Get the groups feature
     * @return FlarumTagsManager  The groups feature
     */
    public function getGroupsManagement():FlarumGroupsManager{
        return $this->flarumGroupManagement;
    }

    /**
     * Get the posts feature
     * @return FlarumPostsManager  The groups feature
     */
    public function getPostsManagement():FlarumPostsManager{
        return $this->flarumPostManagement;
    }



    /**
     * Return the configuration
     *
     * @return FlarumConnectorConfig    Return the current config
     */
    public function getConfig():FlarumConnectorConfig{
        return $this->config;
    }


    /**
     * Realize a login operation asynchronously
     *
     * @param string  $login
     * @param string  $password
     * @param boolean $setCookie    Set the cookie directly if true
     * @return \GuzzleHttp\Promise\promiseinterface               A token or false if the login operation is a failure
     */
    public function login(string $login,string $password,bool $setCookie):\GuzzleHttp\Promise\promiseinterface{
        $res = $this->flarumSSO->login($login,$password);
        return $res->then(function ($res) use ($setCookie){
            if($setCookie && !($res instanceof \Exception)){
                setcookie(self::FLARUM_COOKIE, $res->token, time()+$this->config->flarumLifeTime * 60 * 60 * 24, '/', $this->config->rootDomain);
            }
            if($res instanceof \Exception){
                $this->logger->debug('Error while login :'.$res->getMessage());
            }  else{
                $this->setToken($res);
            }
            return $res;
        }, function (\Exception $e){
            $this->logger->debug('Error while login :'.$e->getMessage());
            return $e;
        });

    }

    /**
     * Create a user
     *
     * @param string $login The login from the user
     * @param string $password The password from the user
     * @param string $email The email to set as user email
     * @return \GuzzleHttp\Promise\promiseinterface               A token or false if the login operation is a failure
     * @throws Exceptions\InvalidUserException
     */
     public function signup(string $login,string $password,string $email):\GuzzleHttp\Promise\promiseinterface{
        $res =  $this->flarumSSO->signup($login,$password,$email)->wait();
        if($res instanceof \Exception){
            $this->logger->debug('Error while signup :'.$res->getMessage());
        } 
        return $res;
    }

    /**
     * Logout from the forum
     *
     * @return void
     */
    public function logout(){
        $this->setToken();
        unset($_COOKIE[self::FLARUM_COOKIE]);
        setcookie(self::FLARUM_COOKIE, '', time() - 10, '/', $this->config->rootDomain);
    }

    /**
     * Set the user on all the features
     *
     * @param FlarumToken|null $token  The user to set
     * @return void
     */
    private function setToken(FlarumToken $token = null){
        foreach($this->listenners as $feature){
            $feature->setToken($token);
        }
    }
}
