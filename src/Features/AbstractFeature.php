<?php
namespace FlarumConnection\Features;
use \FlarumConnection\Models\FlarumToken;
use \FlarumConnection\Models\FlarumConnectorConfig;
use \Psr\Log\LoggerInterface;
use \GuzzleHttp\Client;

abstract class AbstractFeature{

     /**
     * Configuration of the library
     *
     * @var FlarumConnectorConfig
     */
    protected $config;

    /**
     * Logger (PSR3 interface)
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Http client
     *
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * The currently connected user
     *
     * @var \FlarumConnection\Models\FlarumToken
     */
    protected $token;


    /**
     * Initialize the feature with the config
     *
     * @param FlarumConnectorConfig $config     Configuration for flarum connector
     * @param LoggerInterface $logger           Logger interface
     */
    protected function init(FlarumConnectorConfig $config,LoggerInterface $logger){
        $this->config = $config;
        $this->logger = $logger;
        $this->http = new Client();
    }

    /**
     * Securely get the token
     *
     * @return bool|FlarumToken
     */
    public function getToken(){
        if($this->token === null || $this->token->userId === null || $this->token->token === null ){
            return false;
        }
        return $this->token;
    }


        
    /**
     * Set the currently connected user token
     *
     * @param FlarumToken $token  The token of the currently connected user
     * @return void
     **/
    public function setToken(FlarumToken $token){
        $this->token = $token;
    }
}
