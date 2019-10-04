<?php

class DateTest extends PHPUnit_Framework_TestCase {

    public function testTrigger(){
        $handler = new \Topolis\Yaml\Handler\Date();
        $this->assertEquals('date', $handler->getTrigger());
    }

    public function testExecute() {
        $handler = new \Topolis\Yaml\Handler\Date();

        $this->assertEquals(date('c'), $handler->execute('now', []));
        $this->assertEquals(date('c', time()+24*3600), $handler->execute('"+1 day"', []));
        $this->assertEquals(date('Ymd'), $handler->execute('now "Ymd"', []));
        $this->assertEquals(date('Ymd'), $handler->execute('now Ymd', []));
        $this->assertEquals('2016-11-22T04:23:12+01:00', $handler->execute('"2016-11-22 04:23:12"', []));
        $this->assertEquals(date('c', 1570186863), $handler->execute('1570186863', []));
    }
}