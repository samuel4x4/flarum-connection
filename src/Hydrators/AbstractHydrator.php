<?php
namespace FlarumConnection\Hydrators;


use WoohooLabs\Yang\JsonApi\Hydrator\ClassHydrator;


/**
 * Root class for Hydrators
 * Class AbstractHydrator
 * @package FlarumConnection\Hydrators
 */
class AbstractHydrator extends ClassHydrator
{
    /**
     * get secuirely a ressource
     * @param \stdClass $object
     * @param string $field
     * @param $default
     * @return mixed
     */
    protected function getRessource(\stdClass $object, string $field, $default = null)
    {
        if (property_exists($object, $field)) {
            return $object->$field;
        }
        return $default;
    }


}