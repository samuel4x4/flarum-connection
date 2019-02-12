<?php

namespace FlarumConnection\Hydrators;

use FlarumConnection\Models\FlarumDiscussion;

use WoohooLabs\Yang\JsonApi\Schema\Document;


/**
 * Class FlarumDiscussionHydrator
 */
class FlarumDiscussionHydrator extends AbstractHydrator
{

    /**
     * Hydrate a FlarumUser
     * @param Document $document
     * @return FlarumDiscussion    The discussion object
     */
    public function hydrate(Document $document): FlarumDiscussion
    {
        $hydrated = parent::hydrateObject($document);
        return $this->createDiscussion($hydrated);
    }

    /**
     * Hydrate a list of flarum discussion
     * @param Document $document    The json api document
     * @return iterable     The list of discussion
     */
    public function hydrateCollection(Document $document): iterable
    {
        $list = parent::hydrateCollection($document);
        $ret = [];
        foreach($list as $element){
            $ret [] = $this->createDiscussion($element);
        }
        return $ret;
    }


    /**
     * Create a FlarumDiscussion based on an hydrated stdclass
     * @param \stdClass $hydrated   The stdclass hydrated from json api
     * @return FlarumDiscussion     The FlarumDiscussion object
     */
    public function createDiscussion(\stdClass $hydrated):?FlarumDiscussion{
        $discussion = new FlarumDiscussion();
        $discussion->init($this->getRessource($hydrated,'title',''),$this->getRessource($hydrated,'content',''),[],$this->getRessource($hydrated,'id',null));
        $discussion->slug = $this->getRessource($hydrated,'slug','');
        $discussion->commentsCount =  $this->getRessource($hydrated,'commentsCount',0);
        $discussion->participantsCount = $this->getRessource($hydrated,'participantsCount',0);
        $discussion->createdAt = $this->getRessource($hydrated,'createdAt') ? strtotime($this->getRessource($hydrated,'createdAt')) : null;
        $discussion->lastPostedAt = $this->getRessource($hydrated,'lastPostedAt') ? strtotime($this->getRessource($hydrated,'lastPostedAt')) : null;
        $discussion->lastReadAt = $this->getRessource($hydrated,'lastReadAt') ? strtotime($this->getRessource($hydrated,'lastReadAt')) : null;
        $discussion->lastReadPostNumber = $this->getRessource($hydrated,'lastReadPostNumber',1);
        $discussion->lastPostNumber = $this->getRessource($hydrated,'lastPostNumber',1);

        if (isset($hydrated->tags)) {
            $tags = $this->getRessource($hydrated,'tags','');
            $tagHydrator = new FlarumTagsHydrator();
            foreach ($tags as $tag) {
                $discussion->tags[] = $tagHydrator->createTag($tag);
            }
        }

        $userHydrator = new FlarumUsersHydrator();
        $discussion->user = $userHydrator->createUser( $this->getRessource($hydrated,'user',null));
        $discussion->lastPostedUser = $userHydrator->createUser( $this->getRessource($hydrated,'lastPostedUser',null));
        return $discussion;
    }
}


