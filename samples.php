<?php
require __DIR__ . '/vendor/autoload.php';

use Topolis\Yaml\Handler\Date;
use Topolis\Yaml\Handler\Environment;
use Topolis\Yaml\Yaml;

// Generate an encrypted value for testing
$ivlen = openssl_cipher_iv_length("aes-256-cbc");
$iv = openssl_random_pseudo_bytes($ivlen);
$encrypted = openssl_encrypt("Hello world!", "aes-256-cbc", "This is my key", OPENSSL_RAW_DATA, $iv);
$encrypted = base64_encode($iv.$encrypted);

// Generate a yaml string for testing
$data = <<<EOT
one:
    hello
two:
    world
three: !decrypt default $encrypted
four: !date now "Y-m-d H:i:s"    
five: !date now Y-m-d
six: !date now
seven: !import test/samples/test.yml
eight: !import test/samples/test.json json
nine: !import test/samples/test.serialized php
ten: !import test/samples/test.yml yaml2
EOT;

// Sample Usage ---------------------

$yaml = new Yaml();

$handler = new \Topolis\Yaml\Handler\Decrypt();
$handler->addKey("This is my key");
$yaml->addHandler($handler);

$handler = new \Topolis\Yaml\Handler\Date();
$yaml->addHandler($handler);

$handler = new \Topolis\Yaml\Handler\Import();
$handler->addParser('yaml2', [$yaml,'parse'], 'D:\\Webserver\www');
$yaml->addHandler($handler);

$parsed = $yaml->parse($data);

print_r($parsed);
