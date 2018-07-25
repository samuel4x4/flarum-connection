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
        $this->createAndLogin();
        $content = uniqid('', false);

        //Admin creation test
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[376],null)->wait();
        $res = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();
        $this->assertTrue($res->content === $content && isset($res->postId),'Test insert of a post as admin '.$res->content);


        //Non admin creation test
        //$res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[1])->wait();
        //$res2 = $this->fConnector->getPostsManagement()->addPost($res0->id,$content)->wait();
        //$this->assertTrue($res2->content === $content.'s3' && isset($res2->postId),'Test insert of a post as user'.$res->content);
    }


    /**
     * Test the creation of a post
     * @throws \Exception
     */
    public function testUpdate(){
        $this->createInstance();
        $this->createAndLogin();
        $content = uniqid('', false);

        //Create a topic
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[376])->wait();
        $res = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();


        //Update a topic as a user
        //$res2 = $this->fConnector->getPostsManagement()->updatePost($content.'s2',$res->postId)->wait();
        //$this->assertTrue($res2->content === $content.'s2' && isset($res2->postId),'Test  update of a post as user'.$res2->content);

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
        $this->createAndLogin();
        $content = uniqid('', false);

        //Create a topic
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[376])->wait();
        $res1 = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();

        //Delete a topic as admin
        $res2 = $this->fConnector->getPostsManagement()->deletePost($res1->postId,null)->wait();
        $this->assertTrue($res2,'Test deletion of a topic as user');

        //Create a new topic
        //$res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[1])->wait();
        //$res = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,true)->wait();

        //Delete a topic as user
        //$res2 = $this->fConnector->getPostsManagement()->deletePost($res->postId)>wait();
        //$this->assertTrue($res2, 'Test deletion of a post as admin');

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
        $res0 = $this->fConnector->getDiscussionManagement()->postTopic("test post","test content",[376])->wait();
        $res1 = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();
        $res2 = $this->fConnector->getPostsManagement()->addPost($res0->id,$content,null)->wait();

        $res3 = $this->fConnector->getPostsManagement()->getPosts($res0->id,null)->wait();
        $this->assertCount(3,$res3);
    }




}