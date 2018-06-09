<?php

/**
 * @backupGlobals disabled
 */
class BrowserExtensionApiControllerGetEmailForDomainTest extends PHPUnit_Framework_TestCase
{

    public function testRunExisting(){
        $machineid = md5(microtime());
        $domain    = "ya.ru";
        $userid    = 666;
        $key       = md5(microtime());

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                array("{$machineid}_userid"),
                array("{$userid}_{$domain}")
            )
            ->will($this->onConsecutiveCalls($userid, $key));

        $ghostConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $ghostConnectorMock->expects($this->exactly(1))
            ->method('setKey')->with($key);
        $ghostConnectorMock->expects($this->exactly(0))
            ->method('getUseridByKey')->willReturn($userid);

        $emailConnectorMock = $this->getMockBuilder('\Models\EmailConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $emailConnectorMock->expects($this->exactly(1))
            ->method('setKey')->with($key);
        $emailConnectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')->willReturn($userid);


        $controller        = new \Controllers\BrowserExtensionApiController($storageMock, $ghostConnectorMock, $emailConnectorMock);
        $args              = [];
        $args['action']    = 'get_email_for_domain';
        $args['domain']    = $domain;
        $args['machineid'] = $machineid;

        $controller->setDomain('example.com');

        $response = $this->getMockBuilder('React\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->exactly(1))
            ->method('writeHead')
            ->with(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'))
            ->willReturn(false);

        $response->expects($this->exactly(1))
            ->method('end')
            ->with(
                $this->logicalAnd(
                    $this->isJson(),
                    $this->stringContains('ok'),
                    $this->stringContains($key.'@'. 'example.com')
                )
            );

        return $controller->run($args, $response);
    }

    public function testRunExistingButEmailConnectorWasDeleted(){
        $machineid = md5(microtime());
        $domain    = "ya.ru";
        $userid    = 666;
        $key       = md5(microtime());

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                array("{$machineid}_userid"),
                array("{$userid}_{$domain}")
            )
            ->will($this->onConsecutiveCalls($userid, $key));

        $ghostConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $ghostConnectorMock->expects($this->any())
            ->method('setKey')->with($key);
        $ghostConnectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')->willReturn(false);

        $emailConnectorMock = $this->getMockBuilder('\Models\EmailConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $emailConnectorMock->expects($this->exactly(1))
            ->method('setKey')->with($key);
        $emailConnectorMock->expects($this->exactly(1))
            ->method('getUseridByKey')->willReturn(false); // important!

        $ghostConnectorMock->expects($this->exactly(1))
            ->method('newKey')->willReturn($key);


        $controller        = new \Controllers\BrowserExtensionApiController($storageMock, $ghostConnectorMock, $emailConnectorMock);
        $args              = [];
        $args['action']    = 'get_email_for_domain';
        $args['domain']    = $domain;
        $args['machineid'] = $machineid;

        $controller->setDomain('example.com');

        $response = $this->getMockBuilder('React\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->exactly(1))
            ->method('writeHead')
            ->with(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'))
            ->willReturn(false);

        $response->expects($this->exactly(1))
            ->method('end')
            ->with(
                $this->logicalAnd(
                    $this->isJson(),
                    $this->stringContains('ok'),
                    $this->stringContains($key.'@'. 'example.com')
                )
            );

        return $controller->run($args, $response);
    }

    public function testRunUserExistsButNoKeyForDomain()
    {
        $machineid = md5(microtime());
        $domain    = "ya.ru";
        $userid    = 666;
        $key       = md5(microtime());

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                array("{$machineid}_userid"),
                array("{$userid}_{$domain}")
            )
            ->will($this->onConsecutiveCalls($userid, false));


        $storageMock->expects($this->exactly(1))
            ->method('set')
            ->withConsecutive(
                array("{$userid}_{$domain}", $key)
            );


        $ghostConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $ghostConnectorMock->expects($this->exactly(1))
            ->method('setUserid')
            ->with($userid)
            ->willReturnSelf();

        $ghostConnectorMock->expects($this->exactly(1))
            ->method('newKey')
            ->willReturn($key);

        $ghostConnectorMock->expects($this->exactly(1))
            ->method('setKey')
            ->with($key);

        $ghostConnectorMock->expects($this->exactly(1))
            ->method('saveConnectorNameByKey')
            ->with($domain);

        $controller        = new \Controllers\BrowserExtensionApiController($storageMock, $ghostConnectorMock);
        $args              = [];
        $args['action']    = 'get_email_for_domain';
        $args['domain']    = $domain;
        $args['machineid'] = $machineid;

        $controller->setDomain('example.com');

        $response = $this->getMockBuilder('React\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->exactly(1))
            ->method('writeHead')
            ->with(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'))
            ->willReturn(false);

        $response->expects($this->exactly(1))
            ->method('end')
            ->with(
                $this->logicalAnd(
                    $this->isJson(),
                    $this->stringContains('ok'),
                    $this->stringContains($key.'@'. 'example.com')
                )
            );

        return $controller->run($args, $response);
    }

    public function testRunUserMissing()
    {
        $machineid = md5(microtime());
        $domain    = "ya.ru";

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                array("{$machineid}_userid")
            )
            ->willReturn(false);

        $ghostConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $controller        = new \Controllers\BrowserExtensionApiController($storageMock, $ghostConnectorMock);
        $args              = [];
        $args['action']    = 'get_email_for_domain';
        $args['domain']    = $domain;
        $args['machineid'] = $machineid;

        $controller->setDomain('example.com');

        $response = $this->getMockBuilder('React\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->exactly(1))
            ->method('writeHead')
            ->with(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'))
            ->willReturn(false);

        $response->expects($this->exactly(1))
            ->method('end')
            ->with(
                $this->logicalAnd(
                    $this->isJson(),
                    $this->stringContains('error')
                )
            );

        return $controller->run($args, $response);
    }

}