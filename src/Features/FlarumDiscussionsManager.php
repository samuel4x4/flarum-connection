<?php

namespace FlarumConnection\Features;




use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumDiscussion;


use FlarumConnection\Models\FlarumTag;
use \Psr\Log\LoggerInterface;


/**
 * Handle topics & posts features of Flarum
 */
class FlarumDiscussionsManager extends AbstractFeature
{

    /**
     * Path for Discussions
     */
    public const DISCUSSIONS_PATH = '/api/discussions';

    /**
     * Initialize the feature with the config
     *
     * @param FlarumConnectorConfig $config Configuration for flarum connector
     * @param LoggerInterface $logger Logger interface
     */
    public function __construct(FlarumConnectorConfig $config, LoggerInterface $logger)
    {
        $this->init($config, $logger);
    }

    /**
     * @param string $title
     * @param string $tag
     * @param int|null $user
     * @return FlarumDiscussion|null
     * @throws \Exception
     */
    public function getTopicByTitleAndTag(string $title, string $tag, ?int $user = null): ?FlarumDiscussion
    {
        /** @var FlarumDiscussion[] $tags */
        $topics = $this->getDiscussions($tag, 0, $user)->wait();

        foreach ($topics as $topic) {
            if ($topic->title == $title) {
                return $topic;
            }
        }

        return null;
    }

    /**
     * @param string $title
     * @param FlarumTag $tag
     * @param string $content
     * @param int|null $user
     * @return FlarumDiscussion
     * @throws \Exception
     */
    public function getOrAddTopicByTitleAndTag(string $title, FlarumTag $tag, string $content, ?int $user = null): FlarumDiscussion
    {
        return $this->getTopicByTitleAndTag($title, $tag->name, $user) ?? $this->postTopic($title, $content, [$tag->tagId], $user)->wait();
    }

    /**
     * Post a new topic
     *
     * @param string $title Title of the topic
     * @param string $content Content of the topic
     * @param array $tags Tags associated (array of int)
     * @param int|null $user    The user that will call the API
     * @return \Http\Promise\Promise    A  promise of  a discussion
     */
    public function postTopic(string $title, string $content, array $tags,int $user = null): \Http\Promise\Promise
    {
        $disc = new FlarumDiscussion();
        $disc->init($title, $content, $tags);

        return $this->insert($this->config->flarumUrl . self::DISCUSSIONS_PATH ,$disc,201,$user);
    }

    /**
     * Delete a topic
     * @param int $id The id of the topic to delete
     * @param int|null $user        The user to use to delete a topic
     * @return \Http\Promise\Promise    A promise of a boolean
     */
    public function deleteTopic(int $id,?int $user = null): \Http\Promise\Promise
    {
        return $this->delete($this->config->flarumUrl . self::DISCUSSIONS_PATH . '/' . $id, new FlarumDiscussion(),204,$user);
    }


    /**
     * Update a topic
     * @param int $id The id of the topic to update
     * @param string $title The title of the topic
     * @param string $content The content of the topic
     * @param array $tags Tags to set
     * @param int|null $user
     * @return \Http\Promise\Promise
     */
    public function updateTopic(int $id, string $title, string $content, array $tags,?int $user = null): \Http\Promise\Promise
    {
        $disc = new FlarumDiscussion();
        $disc->init($title, $content, $tags);
        return $this->update($this->config->flarumUrl . self::DISCUSSIONS_PATH . '/' . $id,$disc,200,$user);

    }

    /**
     * Return a list of discussions associated to a tag
     * @param string $tag       The tag of the discussion
     * @param int $offset       The offset (discussion position start)
     * @param int|null $user    The user that will be used to retrieve the discussion
     * @return \Http\Promise\Promise    A promise of a list of discussion
     */
    public function getDiscussions(string $tag, int $offset = 0,?int $user = null): \Http\Promise\Promise
    {
        return $this->getAll($this->getUri($tag, $offset), new FlarumDiscussion(), $user);
    }


    /**
     * Build url for discussion tag search
     * @param null|string $tag      The tag to select (could be empty
     * @param int $offset           The offset (discussion position start)
     * @return string               The uri to call
     */
    private function getUri(?string $tag, int $offset = 0): string
    {
        $uri = $this->config->flarumUrl . self::DISCUSSIONS_PATH . '?include=' . urlencode('startUser,lastUser,startPost,tags');
        if ($tag === null) {
            $uri .= '&tags&&';
        } else {
            $uri = $uri . '&' . urlencode('filter[q]') . '=' . urlencode('tag' . ':' . $tag);
        }
        if ($offset !== 0) {
            $uri = $uri . '&' . urlencode('page[offset]=') . $offset;
        }
        return $uri;

    }
}
