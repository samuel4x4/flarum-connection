<?php

use \FlarumConnection\Models\FlarumUser;
use \FlarumConnection\FlarumConnector;

use \Wa72\SimpleLogger\EchoLogger;

trait TestRoot{

    /**
     * Connector for Flarum
     * @var FlarumConnector
     */
    protected $fConnector;

    /**
     * The created tag for the test
     * @var
     */
    protected $tag;

    /**
     * Configuration for the tests
     * @var array
     */
    protected $configTest;

    /**
     * Create an instance of the connector for the tests
     */
    protected function createInstance(){
        $this->configTest = (include 'Config/configTest.php');
        if($this->fConnector === null){
            $logger = new EchoLogger(\Psr\Log\LogLevel::DEBUG);
            $config = new \FlarumConnection\Models\FlarumConnectorConfig (
                $this->configTest['flarumUrl'],
                $this->configTest['flarumRootDomain'],
                $this->configTest['flarumApiKey'],
                $this->configTest['flarumDefaultUser'],
                $this->configTest['flarumLifeTime']);
            $this->fConnector = new FlarumConnector($config,$logger,null);
        }

    }

    /**
     * Create a new user
     * @return mixed        A user
     * @throws \Exception
     */
    protected function createUser(){
        $user = uniqid('', true);
        $mail = $user.'@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user,$pass,$mail)->wait();

        if($result instanceof FlarumUser){
            return $result;
        }

        if($result instanceof \Exception) {
            throw $result;
        } else {
            throw new \RuntimeException('Unknown exception');
        }
    }


    /**
     * Log the user
     * @param string $user The user login
     * @param string $pass The user password
     * @return mixed        A user
     * @throws \Exception
     */
    protected function login(string $user, string $pass){
        $result = $this->fConnector->getSSO()->login($user,$pass);
        if($result instanceof \FlarumConnection\Models\FlarumToken){
            return $result;
        } else if($result instanceof \Exception) {
            throw $result;
        } else {
            throw new \RuntimeException('Unknown exception');
        }
    }

    /**
     * Create a user and login
     * @return mixed        A user
     * @throws \Exception
     */
    protected function createAndLogin(){
        $user = uniqid('', false);
        $mail = $user.'@laborange.fr';
        $pass = 'tata2tata';
        $result = $this->fConnector->getSSO()->signup($user,$pass,$mail)->wait();
        if($result instanceof FlarumUser){
            $result = $this->fConnector->getSSO()->login($user,$pass,false)->wait();
            if($result instanceof \FlarumConnection\Models\FlarumToken){
                return $result;
            }

            if($result instanceof \Exception) {
                throw $result;
            } else {
                throw new \RuntimeException('Unknown exception');
            }
        } else if($result instanceof \Exception) {
            throw $result;
        } else {
            throw new \RuntimeException('Unknown exception');
        }
    }
}

