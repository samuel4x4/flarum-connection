<?php
declare(strict_types=1);

namespace FlarumConnection\Tests;
require '../vendor/autoload.php';
require_once 'Helpers/TestRoot.php';

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
        $res = $this->fConnector->getTagsManagement()->addTag($nameTag,$nameTag,'My tag','#efcdef',false,true)->wait();
        $this->assertInstanceOf(FlarumTag::class,$res);
        $this->assertEquals($res->name,$nameTag);
        $this->assertEquals($res->slug,$nameTag);
        $this->assertEquals($res->color,'#efcdef');
        $this->assertNotEmpty($res->id);

    }

    /**
     * Test the retrieval of tags
     */
    public function testGet(){
        $this->createInstance();

        $res = $this->fConnector->getTagsManagement()->getTags()->wait();
        $this->assertInternalType('array',$res) ;

    }


}