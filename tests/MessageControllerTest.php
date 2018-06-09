<?php

/**
 * @backupGlobals disabled
 */
class MessageControllerTest extends PHPUnit_Framework_TestCase
{

    public function testRun(){
        $key    = 666;
        $userid = 123;

        $message = array(
            "key" => $key,
            "data" => [
                'html' => 'body', 'subject' => 'sbj',
                'from' => array(0 => array('address' => 'frm')),
                'to' => array(0 => array('address' => 't0'))
            ]
        );

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $telegramMock = $this->getMockBuilder('\Telegram\Bot\Api')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock = $this->getMockBuilder('\Models\Connector')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key)
            ->willReturnSelf();

        $connectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')
            ->willReturn($userid);

        $telegramMock->expects($this->exactly(1))
            ->method('sendMessage');

        $emailConnectorMock = $this->getMockBuilder('\Models\EmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $ghostEmailConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $conroller = new \Controllers\MessageController($storageMock, $telegramMock, $connectorMock, $emailConnectorMock, $ghostEmailConnectorMock);
        $conroller->run($message);
    }


    public function testRunEmailConnector(){
        $key    = 666;
        $userid = false;
        $userid2 = 123; // from email connector

        $message = array(
            "key" => $key,
            "data" => [
                'html' => 'body', 'subject' => 'sbj',
                'from' => array(0 => array('address' => 'frm')),
                'to' => array(0 => array('address' => 't0'))
            ]
        );

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $telegramMock = $this->getMockBuilder('\Telegram\Bot\Api')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock = $this->getMockBuilder('\Models\Connector')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key)
            ->willReturnSelf();

        $connectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')
            ->willReturn($userid);

        $emailConnectorMock = $this->getMockBuilder('\Models\EmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $emailConnectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key)
            ->willReturnSelf();

        $emailConnectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')
            ->willReturn($userid2);


        $telegramMock->expects($this->exactly(1))
            ->method('sendMessage');

        $ghostEmailConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $conroller = new \Controllers\MessageController($storageMock, $telegramMock, $connectorMock, $emailConnectorMock, $ghostEmailConnectorMock);
        $conroller->run($message );
    }


    public function testRunGhostEmailConnector(){
        $key    = 666;
        $userid = false;
        $userid2 = false;
        $userid3 = 123; // from ghost email connector
        $connectorname = "connectorname";

        $message = array(
            "key" => $key,
            "data" => [
                'html' => 'body', 'subject' => 'sbj',
                'from' => array(0 => array('address' => 'frm')),
                'to' => array(0 => array('address' => 't0'))
            ]
        );

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $telegramMock = $this->getMockBuilder('\Telegram\Bot\Api')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock = $this->getMockBuilder('\Models\Connector')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key)
            ->willReturnSelf();

        $connectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')
            ->willReturn($userid);

        $emailConnectorMock = $this->getMockBuilder('\Models\EmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $emailConnectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key)
            ->willReturnSelf();

        $emailConnectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')
            ->willReturn($userid2);

        //

        $ghostEmailConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $ghostEmailConnectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key)
            ->willReturnSelf();

        $ghostEmailConnectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')
            ->willReturn($userid3);

        // now, we should expect that connector will be deleted from  ghostEmailConnector and moved to emailConnector

        $ghostEmailConnectorMock->expects($this->exactly(1))
            ->method('getConnectorNameByKey')
            ->willReturn($connectorname);

        $ghostEmailConnectorMock->expects($this->exactly(1))
            ->method('setUserid')
            ->willReturn($userid3);

        $ghostEmailConnectorMock->expects($this->exactly(1))
            ->method('deleteConnector');

        $emailConnectorMock->expects($this->exactly(1))
            ->method('setUserid')
            ->willReturn($userid3);

        $emailConnectorMock->expects($this->exactly(1))
            ->method('newKey')
            ->with($key);

        $emailConnectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key);

        $emailConnectorMock->expects($this->exactly(1))
            ->method('saveConnectorNameByKey')
            ->with($connectorname);
        // done transitioning connector

        $telegramMock->expects($this->exactly(1))
            ->method('sendMessage');

        $conroller = new \Controllers\MessageController($storageMock, $telegramMock, $connectorMock, $emailConnectorMock, $ghostEmailConnectorMock);
        $conroller->run($message);
    }


    public function testRunWithDataHtml(){
        $key    = 666;
        $userid = 123;

        $message = array(
            "key" => $key,
            "data" => [
                'html' => 'body', 'subject' => 'sbj',
                'from' => array(0 => array('address' => 'frm')),
                'to' => array(0 => array('address' => 't0'))
            ]
        );

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(1)) // html email saved to storage has expiry
            ->method('expire')
            ->with($this->anything(), 43200);

        $telegramMock = $this->getMockBuilder('\Telegram\Bot\Api')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock = $this->getMockBuilder('\Models\Connector')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key)
            ->willReturnSelf();

        $connectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')
            ->willReturn($userid);

        $telegramMock->expects($this->exactly(1))
            ->method('sendMessage')
            ->will($this->returnCallback(function($msg) { // testing here what message we've built to be passed to Telegram
                PHPUnit_Framework_Assert::assertArrayHasKey('chat_id', $msg);
                PHPUnit_Framework_Assert::assertArrayHasKey('text', $msg);
                PHPUnit_Framework_Assert::assertEquals($msg['parse_mode'], 'HTML');
                PHPUnit_Framework_Assert::assertContains('<b>sbj</b>', $msg['text']);
                PHPUnit_Framework_Assert::assertContains('body', $msg['text']);
                PHPUnit_Framework_Assert::assertContains('frm', $msg['text']);
                PHPUnit_Framework_Assert::assertContains('t0', $msg['text']);
                PHPUnit_Framework_Assert::assertContains('http://domain/email/', $msg['text']);
                PHPUnit_Framework_Assert::assertEquals($msg['disable_web_page_preview'], '1');
            }));
        ;

        $emailConnectorMock = $this->getMockBuilder('\Models\EmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $ghostEmailConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $conroller = new \Controllers\MessageController($storageMock, $telegramMock, $connectorMock, $emailConnectorMock, $ghostEmailConnectorMock, 'http://domain');
        $conroller->run($message);
    }


}
