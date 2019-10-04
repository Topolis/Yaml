# Yaml
A small extension library for symfony yaml. This library adds custom commands to the yaml
format, using symfony's TaggedValue classes.

Yaml Sample:
```
secure:
    message: !decrypt WaXEqRY+dgNjsC/75nAE5k2JlizY1GWRY1FuageWAFI=
environment:
    name: !env COMPUTERNAME
dates:
    current: !date now "Y-m-d H:i:s"    
    formatted: !date now Y-m-d
    currentIso: !date now
imported:
    external: !import test/samples/test.yml
    custom-external: !import test/samples/test.json json
```

Usage:
```
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

```

## Date
Parameters can either be a <datetime> string OR an array with {format: <format>, value: <datetime>}
A <datetime> string can either be a unix timestamp or a string parseable by PHP's strtotime function

Examples:
```
!date 1264322
!date '+1 day'
!date now 'Y-m-d'
```

## Decrypt
Decrypt a encrypted variables in various formats. Supported format's can be determined with
openssl_get_cipher_methods(). The encrypted data consists of the data, prefixed be the IV and base64 encoded.

Add keys to use to the handler before use. You should never add the keys themselves to the Yaml file:
```
$handler = new Decrypt();
$handler->addKey("This is my key", "aes-256-cbc", "default"); // 2md & 3rd parameters are optional
```

You can AES-256-CBC encrypt with this sample php script:
```
$ivlen = openssl_cipher_iv_length("aes-256-cbc");
$iv = openssl_random_pseudo_bytes($ivlen);
$encrypted = openssl_encrypt("Hello world!", "aes-256-cbc", "This is my key", OPENSSL_RAW_DATA, $iv);
echo base64_encode($iv.$encrypted);
```

Usage:
```
# use default key
!decrypt WaXEqRY+dgNjsC/75nAE5k2JlizY1GWRY1FuageWAFI=
# Use the key with id 'greenKey'
!decrypt greenKey WaXEqRY+dgNjsC/75nAE5k2JlizY1GWRY1FuageWAFI=
```

## Environment
Returns the value of the specified environment variable

Example:
```
!env COMPUTERNAME
```
You can optionally specify an array of allowed strings to access inside the constructor

## Import
Imports a file in various formats at this position. By default, these parsers are available:
* **php** - parsed by PHP's unserialize function
* **yaml** - parsed by Symfony Yaml parser (can easily replaced by this packages parser)
* **json** - parsed by json_decode and set to return associative arrays

You can set your own parsers with addParser():
```
$handler = new Import();
$handler->addParser('myformat', [$MyClass, 'myparse'], '/only/inside/here', ['other','params']);
```


Usage:
```
!import <filepath> <optional mode - defaults to yaml>
```
 
Samples:
```
!import myfile
!import myfile json
```