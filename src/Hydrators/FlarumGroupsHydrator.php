<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 14:55
 */

namespace FlarumConnection\Hydrators;


use FlarumConnection\Models\FlarumGroup;
use FlarumConnection\Models\FlarumTag;
use WoohooLabs\Yang\JsonApi\Schema\Document;

/**
 * Hydrate tags
 * @package FlarumConnection\Hydrators
 */
class FlarumGroupsHydrator extends AbstractHydrator
{
    /**
     * Hydrate a Flarum Group
     * @param Document $document
     * @return FlarumGroup The returned group
     */
    public function hydrate(Document $document): FlarumGroup
    {
        $hydrated = parent::hydrateObject($document);
        return $this->createGroup($hydrated);
    }

    /**
     * Hydrate a list of flarum tags
     * @param Document $document
     * @return iterable
     */
    public function hydrateCollection(Document $document): iterable
    {
        $list = parent::hydrateCollection($document);
        $ret = [];
        foreach ($list as $element) {
            $ret [] = $this->createGroup($element);
        }
        return $ret;
    }


    /**
     * Create a tag based on an hydrated object
     * @param   \stdClass $hydrated     Hydrated stdclass
     * @return FlarumGroup  The generated Flarum Group
     */
    public function createGroup(\stdClass $hydrated): ?FlarumGroup
    {
        if($hydrated === null){
            return null;
        }
        $group = new FlarumGroup();
        $group->init(
            $this->getRessource($hydrated, 'nameSingular', ''),
            $this->getRessource($hydrated, 'namePlural', '')
        );
        $group->color = $this->getRessource($hydrated, 'color', '#eeeeee');
        $group->icon =  $this->getRessource($hydrated, 'icon', '');
        $group->groupId = (int)$this->getRessource($hydrated, 'id', null);

        return $group;


    }
}