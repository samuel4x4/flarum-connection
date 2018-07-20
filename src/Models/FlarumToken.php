<?php
namespace FlarumConnection\Models;

use FlarumConnection\Exceptions\InvalidTokenException;

/**
 * Model class for Flarum token
 */
    class FlarumToken{
        /**
         * Id of the user
         *
         * @var int
         */
        public $userId;

        /**
         * Token to use as session
         *
         * @var string
         */
        public $token;

        /**
         * Initialize the token
         *
         * @param integer $userId   Id of the user
         * @param string $token     Token of the user
         * @throws InvalidTokenException    When the feedback from the API is not good return an Invalid Token Exception
         */
        public function __construct(int $userId,string $token){
            if(empty($userId) || empty($token)){
                throw new InvalidTokenException('Invalid token');
            }
            $this->userId = $userId;
            $this->token = $token;
        }

        /**
         * Create a token from JSON output
         *
         * @param object $json The JSON array from API
         * @return \FlarumConnection\Models\FlarumToken     A flarum token
         * @throws InvalidTokenException When the feedback from the API is not good return an Invalid Token Exception
         */
        public static function fromToken(\stdClass $json) : FlarumToken{
            return new FlarumToken($json->userId,$json->token);

        }
    }
