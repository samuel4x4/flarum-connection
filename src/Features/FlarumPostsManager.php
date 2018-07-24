<?php
/**
 * Created by IntelliJ IDEA.
 * User: remy
 * Date: 19/07/18
 * Time: 15:00
 */

namespace FlarumConnection\Features;


use FlarumConnection\Models\FlarumConnectorConfig;

use FlarumConnection\Models\FlarumPost;
use FlarumConnection\Models\FlarumTag;
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
     * @param int $discussionId     The discussion into which to append the post
     * @param string $content       The content to be added
     * @param bool $admin   Indicate if admin mode should be forced
     * @return Promise        The promise of a post or an exception
     * @throws \FlarumConnection\Exceptions\InvalidUserException Trigerred if no users are associated
     */
    public function addPost(int $discussionId,string $content, bool $admin = false): Promise
    {
        $post = new FlarumPost();
        $post->init($content);
        $post->discussionId = $discussionId;

        return $this->insert($this->config->flarumUrl . self::API_POSTS, $post, 201, $admin);

    }

    /**
     * Update a post
     * @param string $content
     * @param int $postId
     * @param bool $admin Use admin mode or not
     * @return Promise        A promise of a tag or of an exception
     * @throws \FlarumConnection\Exceptions\InvalidUserException An exception is trigerred if no user is associated
     */
    public function updatePost(string $content,int $postId, bool $admin = false): Promise
    {
        $post = new FlarumPost();
        $post->init($content);
        $post->postId = $postId;


        return $this->update($this->config->flarumUrl . self::API_POSTS . '/' . $postId, $post, 200, $admin);
    }

    /**
     * Return the list of groups
     * @param int $discussionId     The id of the discussion associated
     * @param bool $admin Use the current user or use admin
     * @return Promise        A list of group
     * @throws \FlarumConnection\Exceptions\InvalidUserException If no users are associated
     */
    public function getPosts(int $discussionId, bool $admin = false): Promise
    {
        return $this->getAll($this->getUriDiscussion($discussionId), new FlarumPost(), $admin);
    }

    /**
     * Delete a post
     * @param int $postId   The id of the post to delete
     * @param bool $admin Use admin mode or not
     * @return Promise
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     */
    public function deletePost(int $postId, bool $admin = false): Promise{
        return $this->delete(
            $this->config->flarumUrl . self::API_POSTS.'/'.$postId,
        new FlarumPost(),
        204,
        $admin);

    }

    /**
     * Return the uri for discussions
     * @param int $discussionId The id of the discussion
     * @return string       The uri to be called
     */
    private function getUriDiscussion(int $discussionId){
        $uri = $this->config->flarumUrl . self::API_POSTS . '?include=';
        $uri = $uri . '&' . urlencode('filter[discussion]') . '=' . urlencode($discussionId);
        return $uri;
       // ?filter%5Buser%5D=2&filter%5Btype%5D=comment&page%5Blimit%5D=20&sort=-time
    }

}