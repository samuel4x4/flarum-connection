<?php

namespace FlarumConnection\Models;

use FlarumConnection\Exceptions\InvalidTokenException;
use FlarumConnection\Hydrators\AbstractHydrator;
use FlarumConnection\Hydrators\FlarumTokenHydrator;
use FlarumConnection\Serializers\AbstractSerializer;
use FlarumConnection\Serializers\FlarumTokenSerializer;

/**
 * Model class for Flarum token
 */
class FlarumToken extends AbstractModel
{
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
     * @param integer $userId Id of the user
     * @param string $token Token of the user
     * @throws InvalidTokenException    When the feedback from the API is not good return an Invalid Token Exception
     */
    public function __construct(int $userId, string $token)
    {
        if (empty($userId) || empty($token)) {
            throw new InvalidTokenException('Invalid token');
        }
        $this->userId = $userId;
        $this->token = $token;
    }

    /**
     * Return the name of the model
     * @return string
     */
    public function getModelName(): string
    {
        return 'Token';
    }

    /**
     * Retrieve the Serializer of the object
     * @return AbstractSerializer
     */
    public function getSerializer(): AbstractSerializer
    {
        return new FlarumTokenSerializer();
    }

    /**
     * Retrieve the hydrator of the object
     * @return AbstractHydrator
     */
    public function getHydrator(): AbstractHydrator
    {
        return new FlarumTokenHydrator();
    }

    /**
     * Create a token from JSON output
     *
     * @param object $json The JSON array from API
     * @return \FlarumConnection\Models\FlarumToken     A flarum token
     * @throws InvalidTokenException When the feedback from the API is not good return an Invalid Token Exception
     */
    public static function fromToken(\stdClass $json): FlarumToken
    {
        return new FlarumToken($json->userId, $json->token);

    }


}
