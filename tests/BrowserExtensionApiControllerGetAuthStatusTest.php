<?php

/**
 * @backupGlobals disabled
 */
class BrowserExtensionApiControllerGetAuthStatusTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException Exception
     */
    public function testExceptions(){
        $c = new \Controllers\BrowserExtensionApiController(false, false);
        $c->run(false, false);
    }



    public function testRunGetAuthStatusExisting()
    {
        $userid = md5(microtime());
        $machineid = md5(microtime());

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

        $controller        = new \Controllers\BrowserExtensionApiController($storageMock, $ghostConnectorMock);
        $args              = [];
        $args['action']    = 'get_auth_status';
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
                    $this->stringContains('ok'),
                    $this->stringContains($userid)
                )
            );

        $controller->setDomain('example.com');
        return $controller->run($args, $response);
    }

    public function testRunGetAuthStatusNonExisting()
    {
        $machineid = md5(microtime());

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
        $args['action']    = 'get_auth_status';
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