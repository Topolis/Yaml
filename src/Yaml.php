<?php

namespace Topolis\Yaml;

use Symfony\Component\Yaml\Tag\TaggedValue;
use  Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Topolis\Yaml\Common\ITagHandler;

class Yaml {

    /* @var ITagHandler[] $handlers */
    protected $handlers = [];

    public function addHandler(ITagHandler $handler){
        $this->handlers[$handler->getTrigger()] = $handler;
    }

    public function parse(string $input, int $flags = 0){
        $data = SymfonyYaml::parse($input, $flags + SymfonyYaml::PARSE_CUSTOM_TAGS);
        return $this->process($data);
    }

    public function parseFile(string $filename, int $flags = 0){
        $data = SymfonyYaml::parseFile($filename, $flags + SymfonyYaml::PARSE_CUSTOM_TAGS);
        return $this->process($data);
    }

    protected function process(array $data){
        array_walk_recursive($data, [$this, 'applyHandlers'], $data);
        return $data;
    }

    /**
     * @param $item
     * @param $key
     * @param $data
     */
    protected function applyHandlers(&$item, $key, $data){
        /* @var TaggedValue $item */

        if(!$item instanceof TaggedValue)
            return;

        if(!isset($this->handlers[$item->getTag()])){
            $item = '!'.$item->getTag().' '.$item->getValue();
            return;
        }

        $handler = $this->handlers[$item->getTag()];
        $item = $handler->execute($item->getValue(), $data);
    }

}