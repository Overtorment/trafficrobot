<?php

/**
 * @backupGlobals disabled
 */
class BrowserExtensionApiControllerGetEmailTest extends PHPUnit_Framework_TestCase
{


    public function testRun(){
        $machineid = md5(microtime());
        $userid    = md5(microtime());
        $key       = md5(microtime());


        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                array("{$machineid}_userid")
            )
            ->willReturn($userid);

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


        $controller        = new \Controllers\BrowserExtensionApiController($storageMock, $ghostConnectorMock);
        $args              = [];
        $args['action']    = 'get_email';
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


    public function testRunUserNotFound(){
        $machineid = md5(microtime());
        $userid    = false;

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                array("{$machineid}_userid")
            )
            ->willReturn($userid);

        $ghostConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $ghostConnectorMock->expects($this->exactly(0))
            ->method('setUserid');

        $ghostConnectorMock->expects($this->exactly(0))
            ->method('newKey');

        $controller        = new \Controllers\BrowserExtensionApiController($storageMock, $ghostConnectorMock);
        $args              = [];
        $args['action']    = 'get_email';
        $args['machineid'] = $machineid;

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

        $controller->setDomain('example.com');
        return $controller->run($args, $response);
    }

}