<?php

namespace Tests;

/**
 * @backupGlobals disabled
 */
class ConnectorTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @expectedException \Exception
     */
    public function testExceptions()
    {
        $con = new \Models\Connector(false);
        $con->deleteConnector();
    }


    /**
     * @expectedException \Exception
     */
    public function testExceptions2()
    {
        $con = new \Models\Connector(false);
        $con->getUseridByKey();
    }


    /**
     * @expectedException \Exception
     */
    public function testExceptions3()
    {
        $con = new \Models\Connector(false);
        $con->saveKeys(false);
    }


    /**
     * @expectedException \Exception
     */
    public function testExceptions4()
    {
        $con = new \Models\Connector(false);
        $con->newKey(false);
    }


    /**
     * @expectedException \Exception
     */
    public function testExceptions5()
    {
        $con = new \Models\Connector(false);
        $con->getKeysByUserid();
    }


    public function testGetUseridByKey()
    {
        $key = md5(rand());

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('get')
            ->with($key.'_userid')
            ->willReturn('return_value');

        $connector = new \Models\Connector($mock);
        $this->assertEquals($connector->setKey($key)->getUseridByKey(), 'return_value');
    }

    public function testGetKeysByUserid()
    {
        $userid = md5(rand());

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('get')
            ->with($userid.'_keys')
            ->willReturn('aaa|bbb|ccc');

        $connector = new \Models\Connector($mock);
        $this->assertEquals($connector->setUserid($userid)->getKeysByUserid(), ['aaa', 'bbb', 'ccc']);
    }

    public function testSaveKeys()
    {
        $userid = md5(rand());
        $keys = ['aaa', 'bbb', 'ccc'];

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(4))
            ->method('set')
            ->withConsecutive(
                array($userid.'_keys', 'aaa|bbb|ccc'),
                array('aaa_userid', $userid),
                array('bbb_userid', $userid),
                array('ccc_userid', $userid)
            )
            ->willReturn(true);

        $connector = new \Models\Connector($mock);
        $connector->setUserid($userid)->saveKeys($keys);
    }

    public function testGenerateRandomKey()
    {
        $connector = new \Models\Connector(false);
        for ($c=0; $c<1000; $c++) {
            $key = $connector->generateRandomKey();
            if (!preg_match('/^[a-z0-9]+$/', $key)) {
                $this->assertTrue(false, "$key doesnt match regexp");
            }
            if (!preg_match('/^[a-z]+$/', $key[0])) {
                $this->assertTrue(false, "$key doesnt match regexp");
            }
        }
    }

    public function testDeleteConnector()
    {
        $userid = md5(rand());
        $key = 'bbb';

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                array($userid.'_keys')
            )
            ->willReturn('aaa|bbb|ccc');

        $storageMock->expects($this->exactly(3))
            ->method('set')
            ->withConsecutive(
                array($userid.'_keys', 'aaa|ccc'),
                array('aaa_userid', $userid),
                array('ccc_userid', $userid)
            )
            ->willReturn(true);

        $storageMock->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive(
                array($key.'_userid'),
                array($key.'_name')
            )
            ->willReturn(true);

        $connector = new \Models\Connector($storageMock);
        $connector->setUserid($userid)->setKey($key);
        $connector->deleteConnector();
    }

    public function testNewKey()
    {
        $userid = md5(rand());

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                array($userid.'_keys')
            )
            ->willReturn(false);

        /** @var \Models\Connector|\PHPUnit_Framework_MockObject_MockObject $connector */
        $connector = $this->getMockBuilder('\Models\Connector')
            ->setConstructorArgs([$mock])
            ->setMethods(['generateRandomKey', 'saveKeys'])
            ->getMock();
        $connector->expects($this->exactly(1))
            ->method('generateRandomKey')
            ->willReturn('randomkey');

        $connector->expects($this->exactly(1))
            ->method('saveKeys')
            ->with(['randomkey']);

        $connector->setUserid($userid);
        $connector->newKey();
    }


    public function testNewPredefinedKey()
    {
        $userid = md5(rand());
        $key = md5(microtime());

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                array($userid.'_keys')
            )
            ->willReturn(false);

        /** @var \Models\Connector|\PHPUnit_Framework_MockObject_MockObject $connector */
        $connector = $this->getMockBuilder('\Models\Connector')
            ->setConstructorArgs([$mock])
            ->setMethods(['generateRandomKey', 'saveKeys'])
            ->getMock();

        $connector->expects($this->exactly(0)) // should not be called at all!
            ->method('generateRandomKey');

        $connector->expects($this->exactly(1))
            ->method('saveKeys')
            ->with([$key]);

        $connector->setUserid($userid);
        $connector->newKey($key);
    }


    public function testNewKeyWithExistingKeys()
    {
        $userid = md5(rand());

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                array($userid.'_keys')
            )
            ->willReturn('aaa|bbb|ccc');

        /** @var \Models\Connector|\PHPUnit_Framework_MockObject_MockObject $connector */
        $connector = $this->getMockBuilder('\Models\Connector')
            ->setConstructorArgs([$mock])
            ->setMethods(['generateRandomKey', 'saveKeys'])
            ->getMock();

        $connector->expects($this->exactly(1))
            ->method('generateRandomKey')
            ->willReturn('randomkey');

        $connector->expects($this->exactly(1))
            ->method('saveKeys')
            ->with(['aaa', 'bbb', 'ccc', 'randomkey']);

        $connector->setUserid($userid);
        $connector->newKey();
    }


    public function testSetConnectorName()
    {
        $name       = '1234567890123456789012345';
        $name_short = '12345678901234567890';
        $key        = md5(microtime());

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly(1))
            ->method('set')
            ->withConsecutive(
                array($key.'_name', $name_short)
            );

        /** @var \Models\Connector $connector */
        $connector = $this->getMockBuilder('\Models\Connector')
            ->setConstructorArgs([$mock])
            ->setMethods(null)
            ->getMock();

        $connector->setKey($key);
        $connector->saveConnectorNameByKey($name);
    }

    public function testSaveAndFetchLastData()
    {
        $key = md5(rand());
        $data = md5(rand());

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('set')
            ->withConsecutive(
                array($key.'_last', $data)
            );

        $mock->expects($this->once())
            ->method('expire')
            ->withConsecutive(
                array($key.'_last', 3600*1)
            );


        $connector = new \Models\Connector($mock);
        $connector->setKey($key)->saveLastData($data);

        // now fetch

        $mock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('get')
            ->withConsecutive(
                array($key.'_last')
            )
            ->willReturn($data);

        $connector = new \Models\Connector($mock);
        $this->assertEquals($connector->setKey($key)->fetchLastData(), $data);
    }
}

