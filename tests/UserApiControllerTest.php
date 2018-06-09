<?php

/**
 * @backupGlobals disabled
 */
class UserApiControllerTest extends PHPUnit_Framework_TestCase
{

    public function testRunExisting()
    {
        $apikey = md5(microtime());
        $userid = md5(microtime());
        $connectorkey = md5(microtime());
        $data = md5(microtime());

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorMock = $this->getMockBuilder('\Models\Connector')
            ->disableOriginalConstructor()
            ->getMock();

        $ghostConnectorMock = $this->getMockBuilder('\Models\GhostEmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $emailConnectorMock = $this->getMockBuilder('\Models\EmailConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $userApiMock = $this->getMockBuilder('\Models\UserApi')
            ->disableOriginalConstructor()
            ->getMock();

        $userApiMock->expects($this->once())
            ->method('setApikey')
            ->with($apikey);

        $userApiMock->expects($this->once())
            ->method('getUseridByApikey')
            ->willReturn($userid);

        $connectorMock->expects($this->once())
            ->method('setKey')
            ->with($connectorkey);

        $connectorMock->expects($this->once())
            ->method('getUseridByKey')
            ->willReturn($userid);

        $connectorMock->expects($this->once())
            ->method('fetchLastData')
            ->willReturn($data);

        // unit under test
        $controller = new \Controllers\UserApiController(
            $storageMock,
            $connectorMock,
            $emailConnectorMock,
            $ghostConnectorMock,
            $userApiMock
        );
        $args              = [];
        $args['action']    = 'get_last';
        $args['apikey'] = $apikey;
        $args['connectorkey'] = $connectorkey;

        $response = $this->getMockBuilder('React\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->exactly(1))
            ->method('writeHead')
            ->with(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'))
            ->willReturn(false);

        $response->expects($this->exactly(1))
            ->method('end')
            ->with(json_encode($data));

        return $controller->run($args, $response);
    }

}
