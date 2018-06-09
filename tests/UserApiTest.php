<?php

namespace Tests;

/**
 * @backupGlobals disabled
 */
class UserApiTest extends \PHPUnit_Framework_TestCase
{

    public function testGetUseridByApikey()
    {
        $apikey = md5(rand());

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('get')
            ->with($apikey.'_userid')
            ->willReturn('return_value');

        $userApi = new \Models\UserApi($mock);
        $this->assertEquals($userApi->setApikey($apikey)->getUseridByApikey(), 'return_value');
    }

    public function testGetApikeyByUserid()
    {
        $userid = md5(rand());

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->once())
            ->method('get')
            ->with($userid.'_apikey')
            ->willReturn('return_value');

        $userApi = new \Models\UserApi($storageMock);
        $this->assertEquals($userApi->setUserid($userid)->getApikeyByUserid(), 'return_value');

        // now checking the case when no api key exists yet, so we need to generate one

        $userid = md5(rand());
        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Models\UserApi|\PHPUnit_Framework_MockObject_MockObject $userApi */
        $userApi = $this->getMockBuilder('\Models\UserApi')
            ->setConstructorArgs([$storageMock])
            ->setMethods(['generateRandomKey'])
            ->getMock();

        $storageMock->expects($this->once())
            ->method('get')
            ->with($userid.'_apikey')
            ->willReturn(false);

        $userApi->expects($this->exactly(1))
            ->method('generateRandomKey')
            ->willReturn('randomkey');

        $storageMock->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                array($userid.'_apikey', 'randomkey'),
                array('randomkey_userid', $userid) // reverse lookup
            );

        $this->assertEquals($userApi->setUserid($userid)->getApikeyByUserid(), 'randomkey');
    }

    public function testGenerateRandomKey()
    {
        $userApi = new \Models\UserApi(false);
        for ($c=0; $c<1000; $c++) {
            $key = $userApi->generateRandomKey();
            if (!preg_match('/^[a-z0-9]+$/', $key)) {
                $this->assertTrue(false, "$key doesnt match regexp");
            }
            if (!preg_match('/^[a-z]+$/', $key[0])) {
                $this->assertTrue(false, "$key doesnt match regexp");
            }
        }
    }
}
