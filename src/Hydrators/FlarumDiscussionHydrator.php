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
     * @return mixed
     */
    public function hydrate(Document $document): FlarumDiscussion
    {
        echo 'hydrate';
        $hydrated = parent::hydrateObject($document);

        return $this->createDiscussion($hydrated);
    }

    /**
     * Hydrate a list of flarum user
     * @param Document $document
     * @return iterable
     */
    public function hydrateCollection(Document $document): iterable
    {
        echo 'hydratec';
        $list = parent::hydrateCollection($document);
        $ret = [];
        foreach($list as $element){
            $ret [] = $this->createDiscussion($element);
        }
        return $ret;
    }


    /**
     * Create a Flarumuser based on an hydrated stdclass
     * @param \stdClass $hydrated
     * @return FlarumDiscussion
     */
    public function createDiscussion(\stdClass $hydrated):?FlarumDiscussion{
        if($hydrated === null){
            return null;
        }
        $discussion = new FlarumDiscussion();
        $discussion->init($this->getRessource($hydrated,'title',''),$this->getRessource($hydrated,'description',''),[],$this->getRessource($hydrated,'id',null));
        $discussion->slug = $this->getRessource($hydrated,'slug','');
        $discussion->commentsCount =  $this->getRessource($hydrated,'commentsCount',0);
        $discussion->participantsCount = $this->getRessource($hydrated,'participantsCount',0);
        $discussion->startTime = $this->getRessource($hydrated,'startTime',0);
        $discussion->lastTime = $this->getRessource($hydrated,'lastTime',0);
        $discussion->lastPostNumber = $this->getRessource($hydrated,'lastPostNumber',1);

        $userHydrator = new FlarumUsersHydrator();
        $discussion->startUser = $userHydrator->createUser( $this->getRessource($hydrated,'startUser',null));
        $discussion->lastUser = $userHydrator->createUser( $this->getRessource($hydrated,'lastUser',null));
        return $discussion;
    }
}


