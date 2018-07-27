<?php
declare(strict_types=1);

namespace FlarumConnection\Tests;
require '../vendor/autoload.php';
require_once 'Helpers/TestRoot.php';

use FlarumConnection\Models\FlarumPermissions;
use FlarumConnection\Models\FlarumTag;
use FlarumConnection\Models\FlarumTagOrder;
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
    public function testCreate()
    {
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getTagsManagement()->addTag($nameTag, $nameTag, 'My tag', '#efcdef', false, true, null)->wait();
        $this->assertInstanceOf(FlarumTag::class, $res);
        $this->assertEquals($res->name, $nameTag);
        $this->assertEquals($res->slug, $nameTag);
        // $this->assertEquals($res->isRestricted,true);
        $this->assertEquals($res->color, '#efcdef');
        $this->assertNotEmpty($res->tagId);

    }

    /**
     * Test the creation of a tag
     * @throws \Exception
     */
    public function testUpdate()
    {
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getTagsManagement()->addTag($nameTag, $nameTag, 'My tag', '#efcdef', false, true, null)->wait();
        $this->assertInstanceOf(FlarumTag::class, $res);
        $this->assertNotEmpty($res->tagId);
        $res2 = $this->fConnector->getTagsManagement()->updateTag($res->name . '2', $res->slug . '2', $res->description . '2', '#000000', false, true, $res->tagId, null)->wait();
        $this->assertEquals($res2->name, $nameTag . '2');
        $this->assertEquals($res2->slug, $nameTag . '2');
        $this->assertEquals($res2->color, '#000000');
        $this->assertEquals($res2->isRestricted, true);
        $this->assertNotEmpty($res2->tagId);

    }

    /**
     * Test the creation of a tag
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->createInstance();
        $this->createAndLogin();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getTagsManagement()->addTag($nameTag, $nameTag, 'My tag', '#efcdef', false, true, null)->wait();
        $this->assertInstanceOf(FlarumTag::class, $res);
        $this->assertNotEmpty($res->tagId);
        $res3 = $this->fConnector->getTagsManagement()->deleteTag($res->tagId, null)->wait();
        $this->assertTrue($res3);
        $res2 = $this->fConnector->getTagsManagement()->getTags(null)->wait();
        $found = false;
        foreach ($res2 as $tag) {
            if ($tag->tagId === $res->tagId) {
                $found = true;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Test the retrieval of tags
     */
    public function testGet()
    {
        $this->createInstance();

        $res = $this->fConnector->getTagsManagement()->getTags(null)->wait();
        $this->assertInternalType('array', $res);

    }

    /**
     * Test the ordering of tags
     */
    public function testOrderTag()
    {
        $tag1 = new FlarumTag();
        $tag1->tagId = 1;
        $tag1->position = 0;

        $tag2 = new FlarumTag();
        $tag2->tagId = 2;
        $tag2->position = 0;
        $tag2->parent = $tag1;

        $tag3 = new FlarumTag();
        $tag3->tagId = 3;
        $tag3->position = 1;

        $order = new FlarumTagOrder([$tag1, $tag2, $tag3]);

        $expected = ['order' => [
            [
                'id' => 1,
                'children' => [2]
            ],
            [
                'id' => 3,
                'children' => []
            ],
        ]];

        $this->assertEquals($expected, $order->toOrderArray(), 'Test initialization of order tag');

        $order->addParentToEnd(4);

        $expected = ['order' => [
            [
                'id' => 1,
                'children' => [2]
            ],
            [
                'id' => 3,
                'children' => []
            ],
            [
                'id' => 4,
                'children' => []
            ],
        ]];

        $this->assertEquals($expected, $order->toOrderArray(), 'Test add child to end of order tag');

        $order->addParentToStart(5);

        $expected = [ 'order' => [
            [
                'id' => 5,
                'children' => []
            ],
            [
                'id' => 1,
                'children' => [2]
            ],
            [
                'id' => 3,
                'children' => []
            ],
            [
                'id' => 4,
                'children' => []
            ],
        ]];

        $this->assertEquals($expected, $order->toOrderArray(), 'Test add child to start of order tag');

        $order->addParentToPosition(6, 0);

        $expected = [ 'order' => [
            [
                'id' => 6,
                'children' => []
            ],
            [
                'id' => 5,
                'children' => []
            ],
            [
                'id' => 1,
                'children' => [2]
            ],
            [
                'id' => 3,
                'children' => []
            ],
            [
                'id' => 4,
                'children' => []
            ]],
        ];

        $this->assertEquals($expected, $order->toOrderArray(), 'Test add child to start position of order tag');

        $order->addParentToPosition(7, 1);

        $expected = ['order' => [
            [
                'id' => 6,
                'children' => []
            ],
            [
                'id' => 7,
                'children' => []
            ],
            [
                'id' => 5,
                'children' => []
            ],
            [
                'id' => 1,
                'children' => [2]
            ],
            [
                'id' => 3,
                'children' => []
            ],
            [
                'id' => 4,
                'children' => []
            ],
        ]];

        $this->assertEquals($expected, $order->toOrderArray(), 'Test add child to a position of order tag');

        $order->addChildToStart(8, 6);
        $order->addChildToEnd(9, 6);


        $order->addChildToEnd(10, 1);
        $order->addChildToStart(11, 1);


        $order->addChildToPosition(12, 1, 0);
        $order->addChildToPosition(13, 1, 1);

        $expected = ['order' => [
            [
                'id' => 6,
                'children' => [8, 9]
            ],
            [
                'id' => 7,
                'children' => []
            ],
            [
                'id' => 5,
                'children' => []
            ],
            [
                'id' => 1,
                'children' => [12, 13, 11, 2, 10]
            ],
            [
                'id' => 3,
                'children' => []
            ],
            [
                'id' => 4,
                'children' => []
            ],
        ]];

        $this->assertEquals($expected, $order->toOrderArray(), 'Test child orders of order tag');

        $order->removeParent(4);
        $order->removeParent(3);
        $order->removeChild(12, 1);
        $order->removeChild(11, 1);
        $order->removeChild(8, 6);
        $order->removeChild(9, 6);

        $expected = ['order' =>
            [
                [
                    'id' => 6,
                    'children' => []
                ],
                [
                    'id' => 7,
                    'children' => []
                ],
                [
                    'id' => 5,
                    'children' => []
                ],
                [
                    'id' => 1,
                    'children' => [13, 2, 10]
                ],

            ]
        ];
        //$this->assertEquals($expected, $order->toOrderArray(), 'Test child removal on order tag');

    }


    /**
     * Test the order
     * @throws \Exception
     */
    public function testGetOrder()
    {
        $this->createInstance();
        $tag = $this->fConnector->getTagsManagement()->getTagOrder(null)->wait();
        $order = $tag->toOrderArray();
        $this->assertNotEmpty($order, 'Test the order tag get list');
    }

    /**
     * Test the order
     * @throws \Exception
     */
    public function testSetOrder()
    {
        $this->createInstance();


        $nameTag = uniqid('', false);
        $tag1 = $this->fConnector->getTagsManagement()->addTag('TAG1' . $nameTag, 'TAG1' . $nameTag, 'My tag', '#efcdef', false, true, null)->wait();
        $tag2 = $this->fConnector->getTagsManagement()->addTag('TAG2' . $nameTag, 'TAG2' . $nameTag, 'My tag', '#efcdef', false, true, null)->wait();
        $tag3 = $this->fConnector->getTagsManagement()->addTag('TAG3' . $nameTag, 'TAG3' . $nameTag, 'My tag', '#efcdef', false, true, null)->wait();

        $tagOrder = $this->fConnector->getTagsManagement()->getTagOrder(null)->wait();
        if ($tagOrder instanceof FlarumTagOrder) {
            $tagOrder->addParentToStart($tag1->tagId);
            $tagOrder->addChildToStart($tag2->tagId, $tag1->tagId);
            $tagOrder->addChildToStart($tag3->tagId, $tag1->tagId);

            $do = $this->fConnector->getTagsManagement()->setTagOrder($tagOrder, null)->wait();
            $this->assertTrue($do, 'Updating order');
            $newTagOrder = $this->fConnector->getTagsManagement()->getTagOrder(null)->wait();
            if ($newTagOrder instanceof FlarumTagOrder) {
                $res = $newTagOrder->toOrderArray();
                $this->assertEquals($res['order'][0]['id'], $tag1->tagId, 'Test that the parent item is good');
                $this->assertEquals($res['order'][0]['children'][0], $tag3->tagId, 'Test that the first child item is good');
                $this->assertEquals($res['order'][0]['children'][1], $tag2->tagId, 'Test that the first child item is good');
            }

        }

    }


    /**
     * test specific rights set
     * @throws \Exception
     * @throws \Throwable
     */
    public function testSpecificRights()
    {
        $this->createInstance();
        $nameTag = 'TAGFORSPECIFICRIGHTS' . uniqid('', false);
        $nameGroup = 'GROUPFORSPECIFICRIGHTS' . uniqid('', false);
        $tag = $this->fConnector->getTagsManagement()->addTag($nameTag, $nameTag, 'My tag', '#efcdef', false, true, null)->wait();
        //Update the tag to set restricted mod
        $tagU = $this->fConnector->getTagsManagement()->updateTag($nameTag, $nameTag, 'My tag', '#efcdef', false, true, $tag->tagId, null)->wait();

        $this->assertNotEmpty($tag->tagId);
        $group1 = $this->fConnector->getGroupsManagement()->addGroup($nameGroup . 'READ', $nameGroup . 's' . 'READ', '#99bbbb', 'angle-double-down', null)->wait();
        $this->assertNotEmpty($group1->groupId);
        $group2 = $this->fConnector->getGroupsManagement()->addGroup($nameGroup . 'RESPOND', $nameGroup . 's' . 'RESPOND', '#9bb1ff', 'angle-down', null)->wait();
        $this->assertNotEmpty($group2->groupId);
        $group3 = $this->fConnector->getGroupsManagement()->addGroup($nameGroup . 'CREATE', $nameGroup . 's' . 'CREATE', '#ffff00', 'angle-up', null)->wait();
        $this->assertNotEmpty($group3->groupId);
        $group4 = $this->fConnector->getGroupsManagement()->addGroup($nameGroup . 'MODERATE', $nameGroup . 's' . 'MODERATE', '#ff6600', 'angle-double-up', null)->wait();
        $this->assertNotEmpty($group4->groupId);


        $flarumpermission = new FlarumPermissions();
        $flarumpermission->setRead([$group1->groupId]);
        $flarumpermission->setRespond([$group2->groupId]);
        $flarumpermission->setCreate([$group3->groupId]);
        $flarumpermission->setModerate([$group4->groupId]);

        $this->fConnector->getTagsManagement()->setTagPermission($tag->tagId, $flarumpermission, null);
    }


}