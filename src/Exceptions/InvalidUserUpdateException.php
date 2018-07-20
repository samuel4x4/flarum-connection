<?php
namespace FlarumConnection\Exceptions;

/**
 * Exception triggered on invalid user update
 */
class InvalidUserUpdateException extends \Exception{
    /**
     * Initialize
     *
     * @param string $message   The message associated to the exception
     */
    public function __construct(string $message){
        parent::__construct($message);
    }
}
