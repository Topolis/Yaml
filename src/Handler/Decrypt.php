<?php

namespace Topolis\Yaml\Handler;

use Topolis\Yaml\Common\ITagHandler;

/**
 * Class Decrypt
 * Decrypt a encrypted variables in various formats. Supported format's can be determined with
 * openssl_get_cipher_methods(). The encrypted data consists of the data, prefixed be the IV and base64 encoded.
 *
 * add keys to use to the handler before use. You should never add the keys themselves to the Yaml file:
 * $handler = new Decrypt();
 * $handler->addKey("This is my key", "aes-256-cbc", "default"); // 2md/3rd parameters are optional
 *
 * You can AES-256-CBC encrypt with this sample php script:
 * $ivlen = openssl_cipher_iv_length("aes-256-cbc");
 * $iv = openssl_random_pseudo_bytes($ivlen);
 * $encrypted = openssl_encrypt("Hello world!", "aes-256-cbc", "This is my key", OPENSSL_RAW_DATA, $iv);
 * echo base64_encode($iv.$encrypted);
 *
 * Example:
 *     # use default key
 *     !decrypt WaXEqRY+dgNjsC/75nAE5k2JlizY1GWRY1FuageWAFI=
 *     # Use the key with id 'greenKey'
 *     !decrypt greenKey WaXEqRY+dgNjsC/75nAE5k2JlizY1GWRY1FuageWAFI=
 *
 * @package Topolis\Yaml\Handler
 */
class Decrypt implements ITagHandler {

    const DEFAULT_MODE = 'aes-256-cbc';

    protected $keys = [];

    public function addKey($key, $cipher = 'aes-256-cbc', $id = 'default'){
        $this->keys[$id] = [
            'key' => $key,
            'cipher' => $cipher
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTrigger(): string {
        return 'decrypt';
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
                $id = 'default';
                $encrypted = $params[0];
                break;
            case 2:
                $id =  $params[0];
                $encrypted = $params[1];
                break;
            default:
                return null;
        }

        // get key and cipher
        $key = $this->keys[$id]['key'] ?? null;
        $cipher = $this->keys[$id]['cipher'] ?? null;

        if(!$key || !$cipher)
            return null;

        $encrypted = base64_decode($encrypted);

        return $this->decrypt($key, $cipher, $encrypted);
    }

    protected function decrypt($key, $cipher, $encrypted){
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = '';

        if($ivlen) {
            $iv = substr($encrypted, 0, $ivlen);
            $encrypted = substr($encrypted, $ivlen);
        }

        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        $errors = [];
        while($msg = openssl_error_string()){
            $errors[] = $msg;
        }
        if($errors)
            throw new \Exception('Error while decrypting: '.implode(", ",$errors));

        return $decrypted;
    }
}