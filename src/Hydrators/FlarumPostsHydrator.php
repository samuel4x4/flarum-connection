<?php

namespace FlarumConnection\Hydrators;



use FlarumConnection\Models\FlarumPost;


use WoohooLabs\Yang\JsonApi\Schema\Document;


/**
 * Class that hydrate Flarum posts
 */
class FlarumPostsHydrator extends AbstractHydrator
{

    /**
     * Hydrate a FlarumPost
     * @param Document $document    The source json API document
     * @return FlarumPost       The returned JSON document
     */
    public function hydrate(Document $document): FlarumPost
    {
        $hydrated = parent::hydrateObject($document);

        return $this->createPost($hydrated);
    }

    /**
     * Hydrate a list of Flarum posts
     * @param Document $document    The source json API document
     * @return iterable     A list of flarum posts
     */
    public function hydrateCollection(Document $document): iterable
    {
        $list = parent::hydrateCollection($document);
        $ret = [];
        foreach($list as $element){
            $ret [] = $this->createPost($element);
        }
        return $ret;
    }

    /**
     * Create a Post object
     * @param \stdClass|null $hydrated      Hydrated Flarum Post
     * @return FlarumPost|null              A flarum post or null
     */
    public function createPost(?\stdClass $hydrated):?FlarumPost{
        if($hydrated === null){
            return null;
        }
        $post = new FlarumPost();
        $post->init($this->getRessource($hydrated, 'content', ''),$this->getRessource($hydrated, 'id', ''));
        $post->contentHtml = $this->getRessource($hydrated, 'contentHtml', '');
        $post->canApprove = $this->getRessource($hydrated, 'canApprove', '');
        $post->canDelete = $this->getRessource($hydrated, 'canDelete', '');
        $post->canFlag = $this->getRessource($hydrated, 'canFlag', '');
        $post->canlike = $this->getRessource($hydrated, 'canLike', '');
        $post->contentType = $this->getRessource($hydrated, 'contentType', '');
        $post->ipAddress = $this->getRessource($hydrated, 'ipAddress', '');
        $post->isApproved = $this->getRessource($hydrated, 'isApproved', '');
        $post->number = $this->getRessource($hydrated, 'number', '');
        $post->time = $this->getRessource($hydrated, 'time', '');

        $userHydrator = new FlarumUsersHydrator();
        $post->user = $userHydrator->createUser($this->getRessource($hydrated,'user',null));

        if (property_exists($hydrated, 'likes')) {
            $likes = $this->getRessource($hydrated,'likes','');
            $post->likesCount = count($likes);
            foreach ($likes as $user) {
                $post->likes[] = $userHydrator->createUser($user);
            }
        }

        $discussionHydrator = new FlarumDiscussionHydrator();
        $post->discussion = $discussionHydrator->createDiscussion($this->getRessource($hydrated,'discussion',null));
        return $post;
    }




}


