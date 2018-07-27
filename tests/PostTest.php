<?php
declare(strict_types=1);

namespace FlarumConnection\Tests;
require '../vendor/autoload.php';
require_once 'Helpers/TestRoot.php';

use FlarumConnection\Models\FlarumDiscussion;
use FlarumConnection\Models\FlarumGroup;

use FlarumConnection\Models\FlarumPost;
use \PHPUnit\Framework\TestCase;
use TestRoot;

/**
 * Test the tag features
 * Class TagsTest
 * @package FlarumConnection\Tests
 */
final class PostTest extends TestCase
{
    use TestRoot;

    /**
     * Test the creation of a post
     * @throws \Exception
     */
    public function testCreate(){

        $this->createInstance();
        $user = $this->createAndLogin();
        $content = uniqid('', false);

        //Admin creation test
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[$this->configTest['testTagId']],null)->wait();
        $res = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();
        $this->assertTrue($res->content === $content && isset($res->postId),'Test insert of a post as admin '.$res->content);


        //Non admin creation test
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[$this->configTest['testTagId']],$user->userId)->wait();
        $res = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();
        $this->assertTrue($res->content === $content && isset($res->postId),'Test insert of a post as admin '.$res->content);
    }


    /**
     * Test the creation of a post
     * @throws \Exception
     */
    public function testUpdate(){
        $this->createInstance();
        $user = $this->createAndLogin();
        $content = uniqid('', false);

        //Create a topic
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[$this->configTest['testTagId']])->wait();
        $res = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();


        //Update a topic as a user
        //Create a topic
        $res10 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[$this->configTest['testTagId']],$user->userId)->wait();
        $res11 = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,$user->userId)->wait();

        $res4 = $this->fConnector->getPostsManagement()->updatePost($content.'s4',$res11->postId,$user->userId)->wait();
        $this->assertTrue($res4->content === $content.'s4' && isset($res4->postId),'Test  update of a post as user'.$res4->content);

        //Update a topic as an admin
        $res3 = $this->fConnector->getPostsManagement()->updatePost($content.'s3',$res->postId,null)->wait();
        $this->assertTrue($res3->content === $content.'s3' && isset($res3->postId),'Test  update of a post as admin'.$res3->content);

    }

    /**
     * Test the delete of a post
     * @throws \Exception
     */
    public function testDelete(){

        $this->createInstance();
        $user = $this->createAndLogin();
        $content = uniqid('', false);

        //Create a topic & post
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[$this->configTest['testTagId']])->wait();
        $res1 = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();

        //Delete a post as admin
        $res2 = $this->fConnector->getPostsManagement()->deletePost($res1->postId,null)->wait();
        $this->assertTrue($res2,'Test deletion of a topic as user');

        //Create a topic & post
        $res3 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[$this->configTest['testTagId']],$user->userId)->wait();
        $res4 = $this->fConnector->getPostsManagement()->addPost($res3->id,$content,$user->userId)->wait();

        //Delete a topic as user
        $res2 = $this->fConnector->getPostsManagement()->deletePost($res4->postId)->wait();
        $this->assertTrue($res2, 'Test deletion of a post as user');

    }

    /**
     * test retrieval of topic posts
     * @throws \Exception
     */
    public function testGet(){
        $this->createInstance();
        $this->createAndLogin();
        $content = uniqid('', false);

        //Create a topic
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[$this->configTest['testTagId']])->wait();
        $res1 = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();
        $res2 = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();

        $res3 = $this->fConnector->getPostsManagement()->getPosts($res0->id,null)->wait();
        $this->assertCount(3,$res3);
    }




}