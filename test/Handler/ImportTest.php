<?php

class ImportTest extends PHPUnit_Framework_TestCase {

    public function testTrigger(){
        $handler = new \Topolis\Yaml\Handler\Import();
        $this->assertEquals('import', $handler->getTrigger());
    }

    /**
     * @throws Exception
     */
    public function testExecuteDefault() {
        $handler = new \Topolis\Yaml\Handler\Import();

        $valueYaml = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__.'/../samples/test.yml');
        $valueJson = json_decode(file_get_contents(__DIR__.'/../samples/test.json'), true);
        $valuePhp = unserialize(file_get_contents(__DIR__.'/../samples/test.serialized'));

        $this->assertEquals($valueYaml, $handler->execute(__DIR__.'/../samples/test.yml', []));
        $this->assertEquals($valueJson, $handler->execute(__DIR__.'/../samples/test.json', []));

        $this->assertEquals($valueJson, $handler->execute(__DIR__.'/../samples/test.json json', []));
        $this->assertEquals($valuePhp, $handler->execute(__DIR__.'/../samples/test.serialized php', []));
        $this->assertEquals($valueYaml, $handler->execute(__DIR__.'/../samples/test.yml yaml', []));
    }

    /**
     * @throws Exception
     */
    public function testCustomParser(){
        $handler = new \Topolis\Yaml\Handler\Import();
        $handler->addParser('mock', function (){
            $args = func_get_args();

            $this->assertEquals('Mock Data', $args[0]);
            $this->assertEquals('A1', $args[1]);
            $this->assertEquals('B2', $args[2]);

            return 'ok';

        }, false, ['A1', 'B2']);

        $this->assertEquals('ok', $handler->execute(__DIR__.'/../samples/test.mock mock', []));
    }

    /**
     * @throws Exception
     */
    public function testJail(){
        $handler = new \Topolis\Yaml\Handler\Import();
        $handler->addParser('ok1', function (){return 'ok1';}, __DIR__.'/../samples');
        $handler->addParser('ok2', function (){return 'ok2';}, '/');
        $handler->addParser('different', function (){return 'different';}, __DIR__.'/../other');
        $handler->addParser('deeper', function (){return 'deeper';}, __DIR__.'/../samples/deeper');

        $this->assertEquals('ok1', $handler->execute(__DIR__.'/../samples/test.mock ok1', []));
        $this->assertEquals('ok2', $handler->execute(__DIR__.'/../samples/test.mock ok2', []));

        $file = __DIR__.'/../samples/test.mock';
        $this->assertException('Exception','Import file "'.$file.'" is outside jail', function() use ($handler,$file){ $handler->execute($file.' deeper', []); });
        $this->assertException('Exception','Import file "'.$file.'" is outside jail', function() use ($handler,$file){ $handler->execute($file.' different', []); });
    }

    // ----------------------------

    /**
     * @param string $type
     * @param string|null $message
     * @param callable $function
     */
    protected function assertException($type, $message, callable $function)
    {
        $exception = null;

        try {
            call_user_func($function);
        } catch (Exception $e) {
            $exception = $e;
        }

        self::assertThat($exception, new PHPUnit_Framework_Constraint_Exception($type));

        if ($message !== null) {
            self::assertThat($exception, new PHPUnit_Framework_Constraint_ExceptionMessage($message));
        }
    }
}