<?php

/**
 * @backupGlobals disabled
 */
class BrowserExceptionTest extends PHPUnit_Framework_TestCase
{


    /**
     * @expectedException Exception
     */
    public function testExceptions(){
        $bc = new \Models\BrowserConnection(false);
        $bc->associate();
    }

    public function testAssociate(){
        $machineid = md5(rand());
        $userid    = md5(rand());

        $storageMock = $this->getMockBuilder('\Models\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $storageMock->expects($this->exactly(1))
            ->method('set')
            ->withConsecutive(
                array("{$machineid}_userid", $userid)
            );

        $bc = new \Models\BrowserConnection($storageMock);
        $bc->setUserid($userid)->setMachineid($machineid);
        $bc->associate();
    }


}