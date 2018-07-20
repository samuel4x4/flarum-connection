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
        $$this->createAndLogin();

        $res = $this->fConnector->getDiscussionManagement()->postTopic('Hello title', 'My content', [1])->wait();

        $this->assertInstanceOf(FlarumDiscussion::class,$res);
        $this->assertEquals('Hello title',$res->title);


    }

    /**
     *  Test the update of a discussion
     * @throws \Exception
     */
    public function testUpdate()
    {
        $this->createInstance();
        $this->createAndLogin();

        $res = $this->fConnector->getDiscussionManagement()->postTopic('Hello title', 'My content', [1])->wait();

        $this->assertInstanceOf( FlarumDiscussion::class,$res);

        $res2 = $this->fConnector->getDiscussionManagement()->updateTopic($res->id,'Hello title2', 'My content2', [1])->wait();
        $this->assertInstanceOf(FlarumDiscussion::class,$res2) ;
        $this->assertEquals('Hello title2',$res2->title);

    }

}

