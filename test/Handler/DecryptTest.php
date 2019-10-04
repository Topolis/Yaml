<?php

use Topolis\Yaml\Handler\Decrypt;

class DecryptTest extends PHPUnit_Framework_TestCase {

    public function testTrigger(){
        $handler = new \Topolis\Yaml\Handler\Decrypt();
        $this->assertEquals('decrypt', $handler->getTrigger());
    }

    public function testExecuteCiphers() {

        // Not testing everything but only some sample ciphers
        $whitelist = [
            'AES-128-CBC',
            'AES-192-CBC',
            'AES-256-CBC',
            'BF-CBC',
            'CAMELLIA-128-CBC',
            'DES-EDE-CBC',
            'IDEA-CBC',
            'RC2-CBC',
            'RC2-ECB',
            'SEED-CBC',
        ];

        $handler = new \Topolis\Yaml\Handler\Decrypt();
        $key = "1234567890ABCDEF";
        $data = "And this is our highly secured message!";

        foreach(openssl_get_cipher_methods() as $idx => $mode){
            if(in_array($mode, $whitelist)){
                $handler->addKey($key, $mode, 'key-'.$idx);
                $encrypted = $this->encrypt($data, $key, $mode);
                $this->assertEquals($data, $handler->execute('key-'.$idx.' '.$encrypted, []), 'Testing mode '.$mode);
            }
        }
    }

    public function testDefault(){
        $handler = new \Topolis\Yaml\Handler\Decrypt();
        $key = "1234567890ABCDEF";
        $data = "And this is our highly secured message! ".date('c');

        $handler->addKey($key);
        $encrypted = $this->encrypt($data, $key, Decrypt::DEFAULT_MODE);
        $this->assertEquals($data, $handler->execute($encrypted, []), 'Testing default');
    }

    protected function encrypt($data, $key, $mode){
        $ivlen = openssl_cipher_iv_length($mode);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt($data, $mode, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv.$encrypted);
    }
}