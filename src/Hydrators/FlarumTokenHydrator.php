<?php

namespace FlarumConnection\Hydrators;

use FlarumConnection\Models\FlarumDiscussion;

use WoohooLabs\Yang\JsonApi\Schema\Document;


/**
 * Class FlarumDiscussionHydrator
 */
class FlarumTokenHydrator extends AbstractHydrator
{

    /**
     * Hydrate a FlarumUser
     * @param Document $document
     * @return mixed
     */
    public function hydrate(Document $document): FlarumDiscussion
    {
        $hydrated = parent::hydrateObject($document);
        return null;
    }

    /**
     * Hydrate a list of flarum user
     * @param Document $document
     * @return iterable
     */
    public function hydrateCollection(Document $document): iterable
    {
        return parent::hydrateCollection($document);
    }


}


