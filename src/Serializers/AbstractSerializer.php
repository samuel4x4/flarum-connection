<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 16:13
 */

namespace FlarumConnection\Serializers;



/**
 * Root class for the serializer
 * Class AbstractSerializer
 * @package FlarumConnection\Serializers
 */
abstract class AbstractSerializer
{
    /**
     * Provide the json body for an update
     * @param object $instance     The instance of the model
     * @return array        The body as array
     */
    abstract public function getBodyUpdate( $instance):array;


    /**
     * Provide the json body for an insert
     * @param object $instance     The instance of the model
     * @return array        The body as array
     */
    abstract public function getBodyInsert( $instance):array;

}