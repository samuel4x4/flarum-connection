<?php

namespace FlarumConnection\Features;


use FlarumConnection\Exceptions\InvalidUserException;

use FlarumConnection\Models\FlarumConnectorConfig;
use FlarumConnection\Models\FlarumDiscussion;


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
     * Post a new topic
     *
     * @param string $title Title of the topic
     * @param string $content Content of the topic
     * @param array $tags Tags associated (array of int)
     * @param int|null $user    The user that will call the API
     * @return \Http\Promise\Promise    A topic
     */
    public function postTopic(string $title, string $content, array $tags,int $user = null): \Http\Promise\Promise
    {
        $disc = new FlarumDiscussion();
        $disc->init($title, $content, $tags);

        return $this->insert($this->config->flarumUrl . self::DISCUSSIONS_PATH ,$disc,201,$user);
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
     * return a list of discussions
     * @param string $tag
     * @param int $offset
     * @param int|null $user
     * @return \Http\Promise\Promise
     */
    public function getDiscussions(string $tag, int $offset = 0,?int $user = null): \Http\Promise\Promise
    {

        return $this->getAll($this->getUri($tag, $offset), new FlarumDiscussion(), $user);
    }


    /**
     * Build url for tag
     * @param null|string $tag
     * @param int $offset
     * @return string
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
