<?php

class EnvironmentTest extends PHPUnit_Framework_TestCase {

    public function testTrigger(){
        $handler = new \Topolis\Yaml\Handler\Environment();
        $this->assertEquals('env', $handler->getTrigger());
    }

    public function testExecute() {
        $handler = new \Topolis\Yaml\Handler\Environment();

        $testvalue = 'MyTestValue123';
        putenv('mytest='.$testvalue);

        $this->assertEquals($testvalue, $handler->execute('mytest', []));
    }

    public function testWhitelist() {
        $handler = new \Topolis\Yaml\Handler\Environment(['testok']);

        putenv('testok=yes');
        putenv('testfail=no');

        $this->assertEquals('yes', $handler->execute('testok', []));
        $this->assertNull($handler->execute('testfail', []));
    }
}