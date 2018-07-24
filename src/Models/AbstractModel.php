<?php
namespace FlarumConnection\Models;
use FlarumConnection\Hydrators\AbstractHydrator;
use FlarumConnection\Serializers\AbstractSerializer;


/**
 * Base class for JSON API model
 */
abstract class AbstractModel
{

    /**
     * Return the model name
     * @return string
     */
    abstract public function getModelName():string;

    /**
     * Retrieve the Serializer of the object
     * @return AbstractSerializer
     */
    abstract public function getSerializer():AbstractSerializer;

    /**
     * Retrieve the hydrator of the object
     * @return AbstractHydrator
     */
    abstract public function getHydrator():AbstractHydrator;






}