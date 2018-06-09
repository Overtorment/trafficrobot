<?php

/**
 * @backupGlobals disabled
 */
class PurifierTest extends PHPUnit_Framework_TestCase
{

    public function testPurify()
    {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        $html = '<b>Simple and short';
        $pure_html = $purifier->purify($html);
        $this->assertEquals($pure_html, '<b>Simple and short</b>');
    }

}