<?php

namespace Topolis\Yaml\Common;

/**
 * Interface ITagHandler
 * @package Topolis\Yaml\Common
 */
interface ITagHandler {

    /**
     * return the trigger word of this TagHandler
     * @return string
     */
    public function getTrigger(): string;

    /**
     * Execute a tag with $value and return the resulting value
     * @param string $value parameters for tag
     * @param array $data fully parsed yaml data
     * @return mixed result of tag operation
     */
    public function execute($value, $data);

}