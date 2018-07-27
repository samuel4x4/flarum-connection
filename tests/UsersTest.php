<?php
declare(strict_types=1);

namespace FlarumConnection\Tests;
require '../vendor/autoload.php';
require_once 'Helpers/TestRoot.php';

use Exception;
use FlarumConnection\Models\FlarumToken;
use \PHPUnit\Framework\TestCase;


use TestRoot;


final class UsersTest extends TestCase
{
    use TestRoot;

    /**
     * Test the login with admin acount
     *
     * @return void
     */
    public function testLogin(): void
    {
        $this->createInstance();
        $token = $this->fConnector->getSSO()->login('admin', 'sunlight')->wait();
        $this->assertEquals($token->userId,1,'Test the login ');

    }

    /**
     * Test the creation of a user (and login afterward)
     *
     * @return void
     * @throws Exception
     */
    public function testUserCreation(): void
    {
        $this->createInstance();
        $user = uniqid('', false);
        $mail = $user . '@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user, $pass, $mail)->wait();
        $this->assertNotInstanceOf(Exception::class, $result);

        $this->assertEquals(
            $result->username,
            $user,
            'Test creation of user'
        );

        $this->assertEquals(
            $result->email,
            $mail,
            'Test creation of user'
        );
        $token = $this->fConnector->getSSO()->login($user, $pass)->wait();
        $this->assertNotInstanceOf(Exception::class, $token);
        $this->assertNotEquals(
            $token,
            false
        );
    }


    /**
     * Test the deletion of a user
     * @throws Exception
     */
    public function testUserDeletion(): void
    {
        $this->createInstance();
        $user = uniqid('', false);
        $mail = $user . '@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user, $pass, $mail)->wait();
        $result2 = $this->fConnector->getSSO()->login($user, $pass, false)->wait();


        $result3 = $this->fConnector->getUserManagement()->deleteUser($result2->userId)->wait();

        $this->assertTrue($result3);

    }


    /**
     * Test the retrieval of a user
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     * @throws Exception
     */
    public function testUserGet(): void
    {
        $this->createInstance();
        $user = uniqid('', false);
        $mail = $user . '@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user, $pass, $mail)->wait();
        $result2 = $this->fConnector->getSSO()->login($user, $pass, false)->wait();

        $this->assertNotEmpty($result2->userId);

        $result3 = $this->fConnector->getUserManagement()->getUser($result2->userId)->wait();
        $this->assertEquals(
            $result3->username,
            $user
        );
        $this->assertEquals(
            $result3->email,
            $mail
        );
        $this->assertEquals(
            $result3->discussionsCount,
            0
        );

    }

    /**
     * Test the update of a user
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     * @throws Exception
     */
    public function testUserUpdate(): void
    {
        $this->createInstance();
        $user = uniqid('', false);
        $mail = $user . '@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user, $pass, $mail)->wait();

        $result2 = $this->fConnector->getSSO()->login($user, $pass, false)->wait();

        $result3 = $this->fConnector->getUserManagement()->updateEmail($user . '@laborange2.fr', $user, $result2->userId,null)->wait();

        $this->assertEquals(
            $result3->email,
            $user . '@laborange2.fr',
            'Test update of a user'
        );

        $result5 = $this->fConnector->getUserManagement()->updatePassword('totototo', $user, $result2->userId,null)->wait();
        $result6 = $this->fConnector->getSSO()->login($user, 'totototo')->wait();
        $this->assertInstanceOf(FlarumToken::class, $result6,'Test new login after password update');


    }

    /**
     * Test the search by user name
     * @throws Exception
     */
    public function testSearchByUserName(): void
    {
        $this->createInstance();
        $result3=  $this->fConnector->getUserManagement()->getUserByUserName('admin',null)->wait();
        $this->assertTrue($result3->userId === 1 && $result3->username === 'admin', 'Assert that search on user works');
    }


    /**
     * Test admin retrieval
     * @throws Exception
     */
    public function testAdminGet(): void
    {
        $this->createInstance();
        $res = $this->fConnector->getUserManagement()->getUser(1,null)->wait();
        $this->assertEquals($res->username, 'admin', 'Test admin retrieval');
    }

    /**
     * test the assignation of a user to a group
     * @throws Exception
     */
    public function testGroupAddAndDelete(): void
    {
        $this->createInstance();
        $nameTag = uniqid('', false);
        $res = $this->fConnector->getGroupsManagement()->addGroup('GroupAddTest_'.$nameTag,$nameTag.'s','#fgae26','bolt',null)->wait();

        $res2 = $this->fConnector->getUserManagement()->addToGroup(1,$res->groupId,null)->wait();
        $found = false;

        foreach($res2->groups as $group){
            if($group->groupId === $res->groupId){
                $found = true;
            }
        }
        $this->assertTrue($found,'Test to add a new group to a user');
        $res3 = $this->fConnector->getUserManagement()->removeFromGroup(1,$res->groupId,null)->wait();
        $found = false;

        foreach($res3->groups as $group){
            if($group->groupId === $res->groupId){
                $found = true;
            }
        }
        $this->assertFalse($found,'Test to remove a group from a user');

    }


}