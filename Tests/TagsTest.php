<?php
declare(strict_types=1);

namespace FlarumConnection\Tests;
require '../vendor/autoload.php';
require_once 'Helpers/TestRoot.php';

use FlarumConnection\Models\FlarumPermissions;
use FlarumConnection\Models\FlarumTag;
use \PHPUnit\Framework\TestCase;
use TestRoot;

/**
 * Test the tag features
 * Class TagsTest
 * @package FlarumConnection\Tests
 */
final class TagsTest extends TestCase
{
    use TestRoot;

    /**
     * Test the creation of a tag
     * @throws \Exception
     */
    public function testCreate(){
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getTagsManagement()->addTag($nameTag,$nameTag,'My tag','#efcdef',false,true,true)->wait();
        $this->assertInstanceOf(FlarumTag::class,$res);
        $this->assertEquals($res->name,$nameTag);
        $this->assertEquals($res->slug,$nameTag);
       // $this->assertEquals($res->isRestricted,true);
        $this->assertEquals($res->color,'#efcdef');
        $this->assertNotEmpty($res->id);

    }

    /**
     * Test the creation of a tag
     * @throws \Exception
     */
    public function testUpdate(){
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getTagsManagement()->addTag($nameTag,$nameTag,'My tag','#efcdef',false,true,true)->wait();
        $this->assertInstanceOf(FlarumTag::class,$res);
        $this->assertNotEmpty($res->id);
        $res2 = $this->fConnector->getTagsManagement()->updateTag($res->name.'2',$res->slug.'2',$res->description.'2','#000000',false,true,$res->id,true)->wait();
        $this->assertEquals($res2->name,$nameTag.'2');
        $this->assertEquals($res2->slug,$nameTag.'2');
        $this->assertEquals($res2->color,'#000000');
        $this->assertEquals($res2->isRestricted,true);
        $this->assertNotEmpty($res2->id);

    }

    /**
     * Test the creation of a tag
     * @throws \Exception
     */
    public function testDelete(){
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getTagsManagement()->addTag($nameTag,$nameTag,'My tag','#efcdef',false,true,true)->wait();
        $this->assertInstanceOf(FlarumTag::class,$res);
        $this->assertNotEmpty($res->id);
        $res3 = $this->fConnector->getTagsManagement()->deleteTag($res->id,true)->wait();
        //$this->assertTrue($res3);
        $res2 = $this->fConnector->getTagsManagement()->getTags(true)->wait();
        $found = false;
        foreach($res2 as $tag){
            if($tag->id === $res->id){
                $found = true;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Test the retrieval of tags
     */
    public function testGet(){
        $this->createInstance();

        $res = $this->fConnector->getTagsManagement()->getTags(true)->wait();
        $this->assertInternalType('array',$res) ;

    }

    /**
     * @throws \Exception
     */
    public function testSpecificRights(){
        $this->createInstance();
        $nameTag = 'TAGFORSPECIFICRIGHTS'.uniqid('', false);
        $nameGroup= 'GROUPFORSPECIFICRIGHTS'.uniqid('', false);
        $tag = $this->fConnector->getTagsManagement()->addTag($nameTag,$nameTag,'My tag','#efcdef',false,true,true)->wait();
        //Update the tag to set restricted mod
        $tagU = $this->fConnector->getTagsManagement()->updateTag($nameTag,$nameTag,'My tag','#efcdef',false,true,$tag->id,true)->wait();

        $this->assertNotEmpty($tag->id);
        $group1 = $this->fConnector->getGroupsManagement()->addGroup($nameGroup.'READ',$nameGroup.'s'.'READ','#99bbbb','angle-double-down',true)->wait();
        $this->assertNotEmpty($group1->groupId);
        $group2 = $this->fConnector->getGroupsManagement()->addGroup($nameGroup.'RESPOND',$nameGroup.'s'.'RESPOND','#9bb1ff','angle-down',true)->wait();
        $this->assertNotEmpty($group2->groupId);
        $group3 = $this->fConnector->getGroupsManagement()->addGroup($nameGroup.'CREATE',$nameGroup.'s'.'CREATE','#ffff00','angle-up',true)->wait();
        $this->assertNotEmpty($group3->groupId);
        $group4 = $this->fConnector->getGroupsManagement()->addGroup($nameGroup.'MODERATE',$nameGroup.'s'.'MODERATE','#ff6600','angle-double-up',true)->wait();
        $this->assertNotEmpty($group4->groupId);


        $flarumpermission = new FlarumPermissions();
        $flarumpermission->setRead([$group1->groupId]);
        $flarumpermission->setRespond([$group2->groupId]);
        $flarumpermission->setCreate([$group3->groupId]);
        $flarumpermission->setModerate([$group4->groupId]);

        $this->fConnector->getTagsManagement()->setTagPermission($tag->id,$flarumpermission,true);
    }


}