<?php

/**
 * @backupGlobals disabled
 */
class StaticControllerTest extends PHPUnit_Framework_TestCase
{

    public function testRunCss()
    {
        $controller = new \Controllers\StaticController();
        $args = [];
        $args['filepath'] = 'style.css';

        $response = $this->getMockBuilder('React\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->exactly(1))
            ->method('writeHead')
            ->with(200, array('Content-Type' => 'text/css'))
            ->willReturn(false);

        $response->expects($this->exactly(1))
            ->method('end')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('body'),
                    $this->stringContains('.header')
                )
            );

        return $controller->run($args, $response);
    }

    public function testRunPng()
    {
        $controller = new \Controllers\StaticController();
        $args = [];
        $args['filepath'] = 'iphone.png';

        $response = $this->getMockBuilder('React\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->exactly(1))
            ->method('writeHead')
            ->with(200, array('Content-Type' => 'image/png'))
            ->willReturn(false);

        $response->expects($this->exactly(1))
            ->method('end')
            ->with($this->stringContains('PNG'));

        return $controller->run($args, $response);
    }

    public function testRunNotFound()
    {
        $controller = new \Controllers\StaticController();
        $args = [];
        $args['filepath'] = 'unexistingFilename.png';

        $response = $this->getMockBuilder('React\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->exactly(1))
            ->method('writeHead')
            ->with(404, array('Content-Type' => 'text/html'))
            ->willReturn(false);

        $response->expects($this->exactly(1))
            ->method('end')
            ->with($this->stringContains('Not found'));

        return $controller->run($args, $response);
    }

}