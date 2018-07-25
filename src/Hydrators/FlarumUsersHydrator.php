<?php

namespace FlarumConnection\Hydrators;

use FlarumConnection\Models\FlarumUser;
use WoohooLabs\Yang\JsonApi\Schema\Document;
class FlarumUsersHydrator extends AbstractHydrator
{
    /**
     * Hydrate a Flarum user
     * @param Document $document
     * @return FlarumUser
     */
    public function hydrate(Document $document): FlarumUser
    {
        $hydrated = parent::hydrateObject($document);
        return  $this->createUser($hydrated);

    }

    /**
     * Hydrate a list of flarum users
     * @param Document $document
     * @return iterable
     */
    public function hydrateCollection(Document $document): iterable
    {
        $list = parent::hydrateCollection($document);
        $ret = [];
        foreach ($list as $element) {
            $ret [] = $this->createUser($element);
        }
        return $ret;
    }

    /**
     * Create a user based on an hydrated objecy
     * @param $element
     * @return FlarumUser   The hydrated user
     */
    public function createUser(?\stdClass $element):?FlarumUser
    {
        if($element === null){
            return null;
        }
        $user = new FlarumUser();
        $user->init($this->getRessource($element,'id',null), $this->getRessource($element,'username',null),$this->getRessource($element,'email',''));
        $user->avatarUrl = $this->getRessource($element,'avatarUrl');
        $user->bio = $this->getRessource($element,'bio');
        $user->joinTime = $this->getRessource($element,'joinTime',0);
        $user->commentsCount =$this->getRessource($element,'commentsCount',0);
        $user->discussionsCount = $this->getRessource($element,'discussionsCount',0);
        $user->lastSeenTime = $this->getRessource($element,'lastSeenTime');
        //$user->readTime = self::parseDate(self::extractAttribute($element,'readTime'));
        $user->unreadNotifications =$this->getRessource($element,'unreadNotificationsCount',0);
        $user->newNotifications =$this->getRessource($element,'newNotificationsCount',0);

        if(\is_array($this->getRessource($element,'groups',0))){
            $user->groups = [];
            $hydratorGroup = new FlarumGroupsHydrator();
            foreach($element->groups as $group){
                $user->groups[] = $hydratorGroup->createGroup($group);
            }
        }
        return $user;
    }
}