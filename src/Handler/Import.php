<?php

namespace Topolis\Yaml\Handler;

use mysql_xdevapi\Exception;
use Topolis\Yaml\Common\ITagHandler;
use Topolis\Yaml\Yaml;

/**
 * Class Import
 * Imports a file in various formats at this position. By default, these parsers are available:
 *     php - parsed by PHP's unserialize function
 *     yaml - parsed by Symfony Yaml parser (can easily replaced by this packages parser)
 *     json - parsed by json_decode and set to return associative arrays
 *
 * You can set your own parsers with addParser():
 * $handler = new Import();
 * $handler->addParser('myformat', [$MyClass, 'myparse'], '/only/inside/here', ['other','params']);
 *
 * Usage:
 * !import <filepath> <optional mode - defaults to yaml>
 *
 * Samples:
 * !import myfile
 * !import myfile json
 *
 * @package Topolis\Yaml\Handler
 */
class Import implements ITagHandler {

    const MODE_YAML = 'yaml';
    const MODE_SERIALIZED = 'php';
    const MODE_JSON = 'json';

    protected $parsers = [];
    protected static $cache = [];

    public function __construct(){
        // Setup default parsers
        $this->addParser(self::MODE_YAML, 'Symfony\Component\Yaml\Yaml::parse');
        $this->addParser(self::MODE_SERIALIZED, 'unserialize');
        $this->addParser(self::MODE_JSON, 'json_decode', false, [true]);
    }

    /**
     * @param $mode
     * @param callable $callback
     */
    public function addParser($mode, callable $callback, $jailpath = false, $parameters = [], $root = false){
        $this->parsers[$mode] = [
            'callback' => $callback,
            'jailpath' => $jailpath,
            'parameters' => $parameters,
            'root' => $root
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTrigger(): string {
        return 'import';
    }

    /**
     * Replace value with specified environment variable
     * @param string $value parameters for tag
     * @param array $data fully parsed yaml data
     * @return mixed result of tag operation
     * @throws \Exception
     */
    public function execute($value, $data){
        $params = str_getcsv($value, ' ', '"', '\\');

        switch (count($params)){
            case 1:
                $file = $params[0];
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                // Try to use file extension if valid
                $mode = isset($this->parsers[$ext]) ? $ext : self::MODE_YAML;
                break;
            case 2:
                $file = $params[0];
                $mode = $params[1];
                break;
            default:
                return null;
        }

        if(!isset($this->parsers[$mode]))
            throw new \Exception('No parser for mode "'.$mode.'" specified');

        $root = $this->parsers[$mode]['root'];
        $file = $root ? $root.DIRECTORY_SEPARATOR.$file : $file;

        $cacheKey = md5($file);
        if(array_key_exists($cacheKey, self::$cache))
            return self::$cache[$cacheKey];

        $jailpath = $this->parsers[$mode]['jailpath'];
        $callback = $this->parsers[$mode]['callback'];
        $parameters = $this->parsers[$mode]['parameters'];

        if($jailpath && !$this->checkJail($file, $jailpath)){
            throw new \Exception('Import file "'.$file.'" is outside jail');
        }

        if(!file_exists($file))
            throw new \Exception('Import file "'.$file.'" does not exist');

        $contents = file_get_contents($file);
        array_unshift($parameters, $contents);

        $result =  call_user_func_array($callback, $parameters);
        self::$cache[$cacheKey] = $result;
        return $result;
    }

    protected function checkJail($path, $jail){
        $path = realpath($path);
        $jail = realpath($jail);

        return strpos($path, $jail) === 0;
    }

    public static function clearCache() {
        self::$cache = [];
    }
}