<?php
declare(strict_types=1);

namespace FlarumConnection\Tests;
require '../vendor/autoload.php';
require_once 'Helpers/TestRoot.php';

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
        try {
            $token = $this->fConnector->login('admin', 'sunlight', false)->wait();
            $this->assertEquals(
                $token->userId,
                1
            );

        } catch (Exception $e) {
            $this->assertEquals(
                0,
                1
            );

        }


    }

    /**
     * Test the creation of a user (and login afterward)
     *
     * @return void
     */
    public function testUserCreation(): void
    {
        $this->createInstance();
        $user = uniqid('', false);
        $mail = $user . '@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user, $pass, $mail)->wait();
        $this->assertNotInstanceOf(\Exception::class, $result);
        $this->assertEquals(
            $result->username,
            $user
        );

        $this->assertEquals(
            $result->email,
            $mail
        );
        $token = $this->fConnector->login($user, $pass, false)->wait();
        $this->assertNotInstanceOf(\Exception::class, $token);
        $this->assertNotEquals(
            $token,
            false
        );
    }


    /**
     * Test the deletion of a user
     */
    public function testUserDeletion(): void
    {
        $this->createInstance();
        $user = uniqid('', false);
        $mail = $user . '@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user, $pass, $mail)->wait();
        $this->assertNotInstanceOf(\Exception::class, $result);
        $this->assertEquals(
            $result->username,
            $user
        );

        $result2 = $this->fConnector->login($user, $pass, false)->wait();
        $this->assertNotInstanceOf(\Exception::class, $result2);

        $this->assertNotEmpty($result2->userId);

        $result3 = $this->fConnector->getUserManagement()->deleteUser($result2->userId)->wait();

        $this->assertTrue($result3);

    }


    /**
     * Test the retrieval of a user
     * @throws \FlarumConnection\Exceptions\InvalidUserException
     */
    public function testUserGet(): void
    {
        $this->createInstance();
        $user = uniqid('', false);
        $mail = $user . '@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user, $pass, $mail)->wait();

        $this->assertEquals(
            $result->username,
            $user
        );

        $result2 = $this->fConnector->login($user, $pass, false)->wait();

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
     */
    public function testUserUpdate(): void
    {
        $this->createInstance();
        $user = uniqid('', false);
        $mail = $user . '@laborange.fr';
        $pass = $user;
        $result = $this->fConnector->getSSO()->signup($user, $pass, $mail)->wait();

        $this->assertEquals(
            $result->username,
            $user
        );

        $result2 = $this->fConnector->login($user, $pass, false)->wait();

        $this->assertNotEmpty($result2->userId);

        $result3 = $this->fConnector->getUserManagement()->updateEmail($user . '@laborange2.fr', $user, $result2->userId)->wait();
        $this->assertNotInstanceOf(\Exception::class, $result3);
        $result4 = $this->fConnector->getUserManagement()->getUser($result2->userId)->wait();
        $this->assertNotInstanceOf(\Exception::class, $result4);
        $this->assertEquals(
            $result4->email,
            $user . '@laborange2.fr'
        );

        $result5 = $this->fConnector->getUserManagement()->updatePassword('totototo', $user, $result2->userId)->wait();
        $this->assertNotInstanceOf(\Exception::class, $result5);
        $this->assertEquals(
            $result4->email,
            $user . '@laborange2.fr'
        );
        $result6 = $this->fConnector->getSSO()->login($user, 'totototo')->wait();
        $this->assertNotInstanceOf(\Exception::class, $result6);


    }


}