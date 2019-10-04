<?php

namespace Topolis\Yaml\Handler;

use Topolis\Yaml\Common\ITagHandler;

/**
 * Class Date
 *
 * Parameters can either be a <datetime> string OR an array with {format: <format>, value: <datetime>}
 * A <datetime> string can either be a unix timestamp or a string parseable by PHP's strtotime function
 * Examples:
 *    !date 1264322
 *    !date +1 day
 *    !date {format: 'Y-m-d', value: 'now'}
 *
 * @package Topolis\Yaml\Handler
 */
class Date implements ITagHandler {

    /**
     * @inheritDoc
     */
    public function getTrigger(): string {
        return 'date';
    }

    /**
     * Replace value with specified environment variable
     * @param array $value parameters for tag
     * @param array $data fully parsed yaml data
     * @return mixed result of tag operation
     */
    public function execute($value, $data){
        $params = str_getcsv($value, ' ', '"', '\\');

        switch (count($params)){
            case 1:
                $time = $params[0];
                $format = 'c';
                break;
            case 2:
                $time = $params[0];
                $format =  $params[1];
                break;
            default:
                return null;
        }

        if(!is_numeric($time) && is_string($time))
            $time = strtotime($time);

        return date($format, $time);
    }
}