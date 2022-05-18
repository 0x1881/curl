<?php
require_once __DIR__.'/vendor/autoload.php';

use \C4N\Curl;

$headers = [
    "TestHeader-1: value 1",
    "TestHeader-2: value 2"
];

$curl = new Curl();
$curl->get('http://google.com/', $headers);


print_r($curl);