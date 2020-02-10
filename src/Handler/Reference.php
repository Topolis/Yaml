<?php

namespace Topolis\Yaml\Handler;

use Topolis\Yaml\Common\ITagHandler;

/**
 * Class Reference
 * Retrieves another value specified by a path and returns it
 * This is needed if you use the !import handler, as it is executed after yaml parsed the main file and wont allow you 
 * to use yaml's native references :(
 * 
 * Example - simple reference (similar to yaml's 'key: *value'):
 * samples:
 *    data: !ref path.to.my.value
 *
 * Example - reference with overrides (similar to yaml's '<<: *value'):
 * samples:
 *    data: !ref
 *        &: path.to.my.value
 *        override: with this
 *        and: with that
 *
 * you can optionally specify an array of references from outside sources on construct()
 *
 * @package Topolis\Yaml\Handler
 */
class Reference implements ITagHandler {

    protected $references = [];

    public function __construct(array $references = []){
        $this->references = $references;
    }

    /**
     * @inheritDoc
     */
    public function getTrigger(): string {
        return 'ref';
    }

    /**
     * Replace value with specified environment variable
     * @param array|string $value parameters for tag
     * @param array $data fully parsed yaml data
     * @return mixed result of tag operation
     * @throws \Exception
     */
    public function execute($value, $data){
        $refKey = false;

        // Support for either an array with ref as '&' key or directly as a string
        if(is_array($value))
            $refKey = $value['&'] ?? false;
        elseif(is_string($value))
            $refKey = $value;

        if(!$refKey)
            return null;

        $result = null;

        // load data from manually specified reference or from referenced data from docuemnt
        if($this->references)
            $result = $this->getFromPath($this->references, $refKey);

        if(!$result)
            $result = $this->getFromPath($data, $refKey);

        if($result === null)
            throw new \Exception('Yaml reference "'.$refKey.'" not found');

        // if loaded data is an array and we also had array data in our TaggedValue, allow them to be merged
        if(is_array($result) && is_array($value)){
            $overrides = $value;
            unset($overrides['&']);

            $result = $overrides + $result;
        }

        return $result;
    }

    /**
     * get a value from a multi dimensional tree-like array structure via a path string (ex.: "folder.folder.key")
     * @param array $array array to search
     * @param string $path path to traverse
     * @param string $seperator (Optional) seperator in path. Default: "."
     * @return array|mixed|null
     * @throws \Exception         if a node from $path is not found in $array
     */
    protected function getFromPath($array, $path, $seperator = "."){

        $nodes = explode($seperator, $path);
        while($path && count($nodes) > 0){
            $node = array_shift($nodes);

            if(!is_array($array) || !isset($array[$node])){
                return null;
            }

            $array = $array[$node];
        }

        return $array;
    }
}