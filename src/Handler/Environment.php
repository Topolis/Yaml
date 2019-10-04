<?php

namespace Topolis\Yaml\Handler;

use Topolis\Yaml\Common\ITagHandler;

/**
 * Class Environment
 * returns the value of the specified environment variable
 * Example:
 *     !env COMPUTERNAME
 *
 * you can optionally specify an array of allowed strings to access inside the constructor
 *
 * @package Topolis\Yaml\Handler
 */
class Environment implements ITagHandler {

    protected $whitelist = [];

    public function __construct(array $whitelist = []){
        $this->whitelist = $whitelist;
    }

    /**
     * @inheritDoc
     */
    public function getTrigger(): string {
        return 'env';
    }

    /**
     * Replace value with specified environment variable
     * @param string $value parameters for tag
     * @param array $data fully parsed yaml data
     * @return mixed result of tag operation
     */
    public function execute($value, $data){

        if($this->whitelist && !in_array($value, $this->whitelist))
            return null;

        return getenv($value);
    }
}