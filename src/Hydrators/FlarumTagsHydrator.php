<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 20/07/18
 * Time: 14:55
 */

namespace FlarumConnection\Hydrators;


use FlarumConnection\Models\FlarumTag;
use WoohooLabs\Yang\JsonApi\Schema\Document;

/**
 * Hydrate tags
 * @package FlarumConnection\Hydrators
 */
class FlarumTagsHydrator extends AbstractHydrator
{
    /**
     * Hydrate a Flarum Tag
     * @param Document $document        The JSON API source document
     * @return FlarumTag    The hydrated FlarumTag
     */
    public function hydrate(Document $document): FlarumTag
    {
        $hydrated = parent::hydrateObject($document);
        return $this->createTag($hydrated);
    }

    /**
     * Hydrate a list of Flarum tags
     * @param Document $document        The JSON API source document
     * @return FlarumTag    The hydrated FlarumTag list
     */
    public function hydrateCollection(Document $document): iterable
    {
        $list = parent::hydrateCollection($document);
        $ret = [];
        foreach ($list as $element) {
            $ret [] = $this->createTag($element);
        }
        return $ret;
    }


    /**
     * Create a tag based on an hydrated object
     * @param $hydrated     The hydrated class
     * * @return FlarumTag    The hydrated FlarumTag
     */
    public function createTag(?\stdClass $hydrated): ?FlarumTag
    {
        if($hydrated === null){
            return null;
        }
        $tag = new FlarumTag();
        $tag->init(
            $this->getRessource($hydrated, 'name', ''),
            $this->getRessource($hydrated, 'slug', ''),
            $this->getRessource($hydrated, 'color', '#eeeeee'),
            $this->getRessource($hydrated, 'description', ''),
            $this->getRessource($hydrated, 'isHidden', false),
            $this->getRessource($hydrated, 'isRestricted', false),
            $this->getRessource($hydrated, 'id', null)
        );
        $tag->parent = $this->createTag($this->getRessource($hydrated, 'parent', null));
        $tag->position =$this->getRessource($hydrated, 'position', 0);
        $tag->discussions_count = $this->getRessource($hydrated, 'discussionCount', 0);
        return $tag;


    }
}