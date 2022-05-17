# PHP Curl
Bot yapımı için kodlanmış bir curl sınıfıdır.

# Kurulum
```
composer require 0x1881/curl
```

# Kullanımı
```php
<?php

require_once 'vendor/autoload.php';

use \C4N\Curl;

$curl = new Curl;
$curl->get('https://httpbin.org/get');
$curl->exec();

echo $curl->getResponse();
```

# Metodlar
```php
$curl::RAW
$curl::JSON
$curl::QUERY
$curl->getCurlError();
$curl->setDefault();
$curl->setMethod('GET');
$curl->setUrl('https://httpbin.org/get');
$curl->setHeader($headers);
$curl->setBody($body, $curl::RAW);
$curl->send('GET', 'https://httpbin.org/get', $headers, $body, $curl::RAW);
$curl->get($url, $headers);
$curl->post($url, $headers, $body, $curl::RAW);
$curl->put($url, $headers, $body, $curl::RAW);
$curl->delete($url, $headers, $body, $curl::RAW);
$curl->patch($url, $headers, $body, $curl::RAW);
$curl->head($url, $headers);
$curl->connect($url, $headers);
$curl->options($url, $headers);
$curl->trace($url, $headers);
$curl->exec();
$curl->setOpt(CURLOPT_URL, 'https://httpbin.org/get');
$curl->getInfo(CURLINFO_HTTP_CODE);
$curl->setDebug(true);
$curl->setCookieFile('./cookie.txt');
$curl->setCookieJar('./cookie.txt');
$curl->setFollow(true);
$curl->setReturn(true);
$curl->setAutoReferer(true);
$curl->setAutoReferrer(true);
$curl->setTimeout(5);
$curl->setConnectTimeout(5);
$curl->setMaxConnect(5);
$curl->setMaxRedirect(5);
$curl->setProxy('https://username:password@127.0.0.1:8080/');
$curl->setProxyType(CURLPROXY_HTTPS);
$curl->setProxyAuth('username', 'password');
$curl->setDns('1.1.1.1', '1.1.4.4');
$curl->getResponse(false);
$curl->getRespJson(false, 0);
$curl->find(['search value'] /* 'search value' */, $source);
$curl->getBetween('<p>', '</p>', false);
$curl->getBetweens('<p>', '</p>', false);
$curl->getEffective();
$curl->getHttpCode();
$curl->getHeader('Accept', 0);
$curl->getHeaders(0);
$curl->getCookie('laravel_session', 0);
$curl->getCookiesRaw(0);
$curl->getCookiesArray(0);
```