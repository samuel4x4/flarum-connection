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
        //$this->createAndLogin();

        $res = $this->fConnector->getDiscussionManagement()->postTopic('Hello title', 'My content', [376],null)->wait();
        $this->assertInstanceOf(FlarumDiscussion::class,$res);
        $this->assertEquals('Hello title',$res->title);


    }

    /**
     *  Test the update of a discussion
     * @throws \Exception
     */
    public function testUpdate(): void
    {
        $this->createInstance();
        $this->createAndLogin();

        $res = $this->fConnector->getDiscussionManagement()->postTopic('Hello title', 'My content', [376],null)->wait();

        $this->assertInstanceOf( FlarumDiscussion::class,$res);
        $this->assertNotEmpty($res->id);

        $res2 = $this->fConnector->getDiscussionManagement()->updateTopic($res->id,'Hello title3', 'My content2', [376],null)->wait();
        $this->assertInstanceOf(FlarumDiscussion::class,$res2) ;
        $this->assertEquals('Hello title3',$res2->title);

    }

    /**
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     * @throws \Exception
     */
    public function testGet(): void
    {
        $this->createInstance();
        $this->createAndLogin();

        $res = $this->fConnector->getDiscussionManagement()->getDiscussions('testag')->wait();
        $this->assertCount(1, $res);


    }
}

