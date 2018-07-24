<?php
namespace FlarumConnection\Hydrators;


use WoohooLabs\Yang\JsonApi\Hydrator\ClassHydrator;
use WoohooLabs\Yang\JsonApi\Schema\Document;

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

    /**
     * Hydrate a document to a std class
     * @param Document $document
     * @return \stdClass
     */
    public function hydrateObject(Document $document):\stdClass
    {
        return parent::hydrateObject($document);
    }

    /**
     * Hydrate a collection document to a list of stdclass
     * @param Document $document
     * @return iterable
     */
    public function hydrateCollection(Document $document): iterable{
        return parent::hydrateCollection($document);
    }

}