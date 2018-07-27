<?php
declare(strict_types=1);

namespace FlarumConnection\Tests;
require '../vendor/autoload.php';
require_once 'Helpers/TestRoot.php';

use FlarumConnection\Models\FlarumGroup;

use \PHPUnit\Framework\TestCase;
use TestRoot;

/**
 * Test the tag features
 * Class TagsTest
 * @package FlarumConnection\Tests
 */
final class GroupTest extends TestCase
{
    use TestRoot;

    /**
     * Test the creation of a group
     * @throws \Exception
     */
    public function testCreate(){
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getGroupsManagement()->addGroup($nameTag,$nameTag.'s','#fgae26','bolt',null)->wait();
        $this->assertInstanceOf(FlarumGroup::class,$res);
        $this->assertEquals($res->nameSingular,$nameTag);
        $this->assertEquals($res->namePlural,$nameTag.'s');
        $this->assertEquals($res->color,'#fgae26');
        $this->assertEquals($res->icon,'bolt');
        $this->assertNotEmpty($res->groupId);

    }

    /**
     * Test the creation of a group
     * @throws \Exception
     */
    public function testUpdate(){
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getGroupsManagement()->addGroup($nameTag,$nameTag.'s','#fgae26','bolt',null)->wait();
        $this->assertInstanceOf(FlarumGroup::class,$res);
        $this->assertEquals($res->nameSingular,$nameTag);
        $this->assertEquals($res->namePlural,$nameTag.'s');
        $this->assertEquals($res->color,'#fgae26');
        $this->assertEquals($res->icon,'bolt');
        $this->assertNotEmpty($res->groupId);

        $res2 = $this->fConnector->getGroupsManagement()->updateGroup($nameTag.'2',$nameTag.'s2','#fgae25','wrench',$res->groupId,null)->wait();
        $this->assertInstanceOf(FlarumGroup::class,$res2);
        $this->assertEquals($res2->nameSingular,$nameTag.'2');
        $this->assertEquals($res2->namePlural,$nameTag.'s2');
        $this->assertEquals($res2->color,'#fgae25');
        $this->assertEquals($res2->icon,'wrench');
        $this->assertNotEmpty($res2->groupId);

    }

    /**
     * Test the creation of a group
     * @throws \Exception
     */
    public function testDelete(){
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getGroupsManagement()->addGroup($nameTag,$nameTag.'s','#fgae26','bolt',null)->wait();
        $this->assertInstanceOf(FlarumGroup::class,$res);
        $this->assertEquals($res->nameSingular,$nameTag);
        $this->assertEquals($res->namePlural,$nameTag.'s');
        $this->assertEquals($res->color,'#fgae26');
        $this->assertEquals($res->icon,'bolt');
        $this->assertNotEmpty($res->groupId);

        $res2 = $this->fConnector->getGroupsManagement()->deleteGroup($res->groupId,null)->wait();
        $this->assertTrue($res2);

        $res3 = $this->fConnector->getGroupsManagement()->getGroups(null)->wait();

        $found = false;
        foreach($res3 as $group){
            if($group->groupId === $res->groupId){
                $found = true;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Test the retrieval of groups
     * @throws \Exception
     */
    public function testGet(){
        $this->createInstance();

        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getGroupsManagement()->getGroups(null)->wait();
        $this->assertInternalType('array',$res) ;

    }


}