<?php

/**
 * @backupGlobals disabled
 */
class DomainZanitizerTest extends PHPUnit_Framework_TestCase
{

    public function testRunExisting(){
        $this->assertEquals(\Models\DomainSanitizer::sanitize('example.com'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('http://example.com'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('http://example.com/'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('https://example.com'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('http://example.com/blah'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('www.example.com/blah'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('www.example.com/'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('/www.example.com/'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('www1.example.com'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('example.com:80'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('example.com:80/blah'), 'example.com');
        $this->assertEquals(\Models\DomainSanitizer::sanitize('http://www.example.com:80/blah'), 'example.com');
    }

}