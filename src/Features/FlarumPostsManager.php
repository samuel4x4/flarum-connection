<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 19/07/18
 * Time: 15:00
 */

namespace FlarumConnection\Features;


use FlarumConnection\Models\FlarumConnectorConfig;

use FlarumConnection\Models\FlarumDiscussion;
use FlarumConnection\Models\FlarumPost;

use Http\Promise\Promise;
use Psr\Log\LoggerInterface;


/**
 * Handle tgroups  management
 * @package FlarumConnection\Features
 */
class FlarumPostsManager extends AbstractFeature
{
    /**
     * Path for Discussions
     */
    public const API_POSTS = '/api/posts';

    /**
     * FlarumGroupsManager constructor.
     * @param FlarumConnectorConfig $config The configuration of the lib
     * @param LoggerInterface $logger The logger
     */
    public function __construct(FlarumConnectorConfig $config, LoggerInterface $logger)
    {
        $this->init($config, $logger);
    }


    /**
     * Add a new Post
     * @param int $discussionId The discussion into which to append the post
     * @param string $content The content to be added
     * @param int|null $user
     * @return Promise        The promise of a post or an exception
     */
    public function addPost(int $discussionId,string $content,?int $user = null): Promise
    {
        $post = new FlarumPost();
        $post->init($content);
        $post->discussion = new FlarumDiscussion();
        $post->discussion->id=$discussionId;

        return $this->insert($this->config->flarumUrl . self::API_POSTS, $post, 201, $user);

    }

    /**
     * Update a post
     * @param string $content
     * @param int $postId
     * @param int|null $user
     * @return Promise        A promise of a tag or of an exception
     */
    public function updatePost(string $content,int $postId,?int $user = null): Promise
    {
        $post = new FlarumPost();
        $post->init($content);
        $post->postId = $postId;


        return $this->update($this->config->flarumUrl . self::API_POSTS . '/' . $postId, $post, 200, $user);
    }

    /**
     * Return the list of groups
     * @param int $discussionId The id of the discussion associated
     * @param int|null $user
     * @return Promise        A list of group
     */
    public function getPosts(int $discussionId,?int $user = null): Promise
    {
        return $this->getAll($this->getUriDiscussion($discussionId), new FlarumPost(), $user);
    }

    /**
     * Delete a post
     * @param int $postId The id of the post to delete
     * @param int|null $user
     * @return Promise
     */
    public function deletePost(int $postId, ?int $user = null): Promise{
        return $this->delete(
            $this->config->flarumUrl . self::API_POSTS.'/'.$postId,
        new FlarumPost(),
        204,
        $user);

    }

    /**
     * Return the uri for discussions
     * @param int $discussionId The id of the discussion
     * @return string       The uri to be called
     */
    private function getUriDiscussion(int $discussionId): string
    {
        $uri = $this->config->flarumUrl . self::API_POSTS . '?include=';
        $uri = $uri . '&' . urlencode('filter[discussion]') . '=' . urlencode((string)$discussionId);
        return $uri;
    }

}