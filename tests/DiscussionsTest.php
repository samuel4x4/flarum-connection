<?php
declare(strict_types=1);

namespace FlarumConnection\Tests;
require '../vendor/autoload.php';

require_once 'Helpers/TestRoot.php';

use FlarumConnection\Models\FlarumDiscussion;
use \PHPUnit\Framework\TestCase;
use TestRoot;


final class DiscussionsTest extends TestCase
{
    use TestRoot;


    /**
     *  Test the creation of a discussion
     * @throws \Exception
     */
    public function testCreate()
    {
        $this->createInstance();
        $user = $this->createAndLogin();

        //test creation as admin
        $res = $this->fConnector->getDiscussionManagement()->postTopic('Hello title', 'My content', [$this->configTest['testTagId']],null)->wait();
        $this->assertInstanceOf(FlarumDiscussion::class,$res);
        $this->assertEquals('Hello title',$res->title, 'Testing discussion creation with admin');

        //test creation as user
        $res = $this->fConnector->getDiscussionManagement()->postTopic('Hello title', 'My content', [$this->configTest['testTagId']],$user->userId)->wait();
        $this->assertInstanceOf(FlarumDiscussion::class,$res);
        $this->assertEquals('Hello title',$res->title, 'Testing discussion creation with user');



    }

    /**
     *  Test the update of a discussion
     * @throws \Exception
     */
    public function testUpdate(): void
    {
        $this->createInstance();
        $user = $this->createAndLogin();

        $res = $this->fConnector->getDiscussionManagement()->postTopic('Hello title', 'My content', [$this->configTest['testTagId']],$user->userId)->wait();

        //Test update as admin
        $res2 = $this->fConnector->getDiscussionManagement()->updateTopic($res->id,'Hello title3', 'My content2', [$this->configTest['testTagId']],null)->wait();
        $this->assertInstanceOf(FlarumDiscussion::class,$res2) ;
        $this->assertEquals('Hello title3',$res2->title, 'Test discussion update as admin');

        //Test update as user
        $res2 = $this->fConnector->getDiscussionManagement()->updateTopic($res->id,'Hello title4', 'My content3', [$this->configTest['testTagId']],null)->wait();
        $this->assertInstanceOf(FlarumDiscussion::class,$res2) ;
        $this->assertEquals('Hello title4',$res2->title,'Test discussion update as user');

    }

    /**
     * Test the retrieval of discussion
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     * @throws \Exception
     */
    public function testGet(): void
    {
        $this->createInstance();
        $user = $this->createAndLogin();

        $res = $this->fConnector->getDiscussionManagement()->getDiscussions($this->configTest['testTagName'],$user->userId)->wait();
        $this->assertTrue(\count($res)>0, 'Test get discussions');

    }
    /**
     * test the delete of a topic
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     * @throws \Exception
     */
    public function testDelete(): void
    {
        $this->createInstance();
        $this->createAndLogin();


        $res = $this->fConnector->getDiscussionManagement()->postTopic('Hello title', 'My content', [$this->configTest['testTagId']],null)->wait();
        $res2 = $this->fConnector->getDiscussionManagement()->deleteTopic($res->id)->wait();
        $this->assertTrue($res2, 'Test delete of a topic');
        $res3 = $this->fConnector->getDiscussionManagement()->getDiscussions($this->configTest['testTagName'])->wait();
        $found = false;
        foreach($res3 as $discussion){
            if($discussion->id === $res->id){
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Test delete of a topic, search for deleted topic');

    }

}

