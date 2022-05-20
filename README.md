# PHP Curl
Bot yapımı için kodlanmış bir curl sınıfıdır.

# Kurulum
```
composer require 0x1881/curl
```

# Kullanımı
```php
<?php

require_once __DIR__.'/vendor/autoload.php';

$curl = new \C4N\Curl;
$curl->get('https://httpbin.org/get')
     ->setDebug(true)
     ->setHeader('User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)')
     ->setUserAgent('Googlebot/2.1 (+http://www.google.com/bot.html)')
     ->setCookieFile('./cookie.txt')
     ->setCookieJar('./cookie.txt')
     ->setReferer('https://httpbin.org/')
     ->setAutoReferer(true)
     ->setTimeout(5)
     ->setProxy('127.0.0.1:8888')
     ->setProxyAuth('user:pass')
     ->exec();

echo $curl->getResponse();
echo $curl->getHttpCode();
echo $curl->getHeader('Location');
echo $curl->getCookie('laravel_session');
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
$curl->getOpt(CURLOPT_URL);
$curl->getInfo(CURLINFO_HTTP_CODE);
$curl->getInfos('http_code');
$curl->setDebug(true);
$curl->setUserAgent('Googlebot/2.1 (+http://www.google.com/bot.html)');
$curl->setCookieFile('./cookie.txt');
$curl->setCookieJar('./cookie.txt');
$curl->setFollow(true);
$curl->setReturn(true);
$curl->setReferer('https://httpbin.org/');
$curl->setAutoReferer(true);
$curl->setTimeout(5);
$curl->setConnectTimeout(5);
$curl->setMaxConnect(5);
$curl->setMaxRedirect(5);
$curl->setProxy('https://username:password@127.0.0.1:8080/');
$curl->setProxyType(CURLPROXY_HTTPS);
$curl->setProxyAuth('username', 'password');
$curl->getResponse(false);
$curl->getRespJson(false);
$curl->find('search value', $source);
$curl->getBetween('<p>', '</p>');
$curl->getBetweens('<p>', '</p>');
$curl->getEffective();
$curl->getHttpCode();
$curl->getHeader('Accept');
$curl->getHeaders();
$curl->getCookie('laravel_session');
$curl->getCookiesRaw();
$curl->getCookiesArray();
```