<?php
namespace FlarumConnection\Exceptions;

/**
 * Exception triggered on invalid discussion creation
 */
class InvalidUpdateException extends \RuntimeException{
    /**
     * Initialize
     *
     * @param string $message   The message associated to the exception
     */
    public function __construct(string $message){
        parent::__construct($message);
    }

}
