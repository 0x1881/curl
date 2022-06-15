# PHP Curl
[![en](https://img.shields.io/badge/lang-en-red.svg)](https://github.com/0x1881/curl/blob/main/README.md)
[![tr](https://img.shields.io/badge/lang-tr-green.svg)](https://github.com/0x1881/curl/blob/main/README.tr.md)

This package can send HTTP requests to a given site using Curl.

It provides functions that can take several types of parameters to configure an HTTP request to that the class will send to an HTTP api.

After setting all the parameters, the class can execute sending of the configured HTTP request.

Coded for educational purposes. The user is responsible for the abuse.

--- 

## Install
```ps1
composer require 0x1881/curl
```
--- 

## Using
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
--- 

## Methods

### [Request Methods](#Details-of-Request-Methods)
- [setDefault](#setdefault)
- [setMethod](#setmethod)
- [setUrl](#seturl)
- [setHeader](#setheader)
- [setBody](#setbody)
- [setOpt](#setOpt)
- [setDebug](#setDebug)
- [setUserAgent](#setUserAgent)
- [setCookie](#setCookie)
- [setCookieFile](#setCookieFile)
- [setCookieJar](#setCookieJar)
- [setFollow](#setFollow)
- [setReturn](#setReturn)
- [setReferer](#setReferer)
- [setAutoReferer](#setAutoReferer)
- [setTimeout](#setTimeout)
- [setConnectTimeout](#setConnectTimeout)
- [setMaxConnect](#setMaxConnect)
- [setMaxRedirect](#setMaxRedirect)
- [setProxy](#setProxy)
- [setProxyType](#setProxyType)
- [setProxyAuth](#setProxyAuth)
- [send](#send)
- [get](#get)
- [post](#post)
- [put](#put)
- [put](#put)
- [delete](#delete)
- [patch](#patch)
- [head](#head)
- [connect](#connect)
- [options](#options)
- [trace](#trace)
- [exec](#exec)

### [Response Methods](#Details-of-Response-Methods)
- [getInfo](#getInfo)
- [getCurlError](#getCurlError)
- [getResponse](#getResponse)
- [getRespJson](#getRespJson)
- [getEffective](#getEffective)
- [getHttpCode](#getHttpCode)
- [getHeader](#getHeader)
- [getHeaders](#getHeaders)
- [getCookie](#getCookie)
- [getCookiesRaw](#getCookiesRaw)
- [getCookiesArray](#getCookiesArray)
- [getBetween](#getBetween)
- [getBetweens](#getBetweens)

### [Other Methods](#Details-of-Other-Methods)
- [find](#find)
- [getOpt](#getOpt)

### [Constants](#Details-of-Constants)
- [RAW](#RAW)
- [JSON](#JSON)
- [QUERY](#QUERY)
--- 

# Detailed Description of Methods

## Details of Request Methods

### setDefault()
Returns the class to its default request settings. It resets the settings to default on every request made.

```php
$curl->setDefault();
```
---

### setMethod()
Used to specify the request type.

#### Kabul edilen türler

- ```GET```, ```POST```, ```PUT```, ```DELETE```, ```PATCH```, ```HEAD```, ```CONNECT```, ```OPTIONS```, ```TRACE```

```php
$curl->setMethod('GET');
```
---

### setUrl()
Specifies the destination of the request.

```php
$curl->setUrl('https://httpbin.org/get');
```
---

### setHeader()
Adds header to the request.

```php
$curl->setHeader('Test-Header: value');
```
or
```php
$curl->setHeader('Test-Header', 'value');
```
or
```php
$headers = [
    'Test-Header: value',
    'Test-Header2: value'
];
$curl->setHeader($headers);
```
---

### setBody()
Adds body to the request. Applies to certain request methods.

**Raw**: The specified body is set for the request as direct plain text.
```php
$curl->setBody('name=Mehmet&lastname=Can');
```
**Query 1**: Sets a body of content type ```x-www-form-urlencoded```. Added as an example comment. If the data is of type array, the method will convert it to the format we specified.
```php
$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->setBody($body);

// name=Mehmet&lastname=Can
```
**Query 2**: Sets a body of content type ```x-www-form-urlencoded```. Added as an example comment.
```php
$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->setBody($body, $curl::QUERY);

// name=Mehmet&lastname=Can
```
**_Json_**: Sets a body in ```json``` format. Added as an example comment.
```php
$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->setBody($body, $curl::JSON);

// {"name":"Mehmet","lastname":"Can"}
```
---

### setOpt()
It is the method that makes the ``curl_setopt`` method of the curl library practical. CURLOPT constants can be used optionally.

```php
$curl->setOpt(CURLOPT_URL, 'https://httpbin.org/get');
```
---

### setDebug()
It sets the constant CURLOPT_VERBOSE from the curl library to true, so it returns optional debug data on terminal lines. The default ``false`` is off.

```php
$curl->setDebug(true);
```
---

### setUserAgent()
Sets the ``User-Agent`` header of the request.

```php
$curl->setUserAgent('Googlebot/2.1 (+http://www.google.com/bot.html)');
```
---

### setCookie()
Adds the specified cookie information to the ``Cookie`` header of the request.

```php
$cookies = "XSRF-TOKEN=OWY4NmQwODE4ODRMjJjZDE1ZDZMGYwMGEwOA==; ci_session=esa2tb3mviicp2cb5abz32g";
$curl->setCookie($cookies);
```
or
```php
$cookie_name = 'XSRF-TOKEN';
$cookie_value = 'OWY4NmQwODE4ODRMjJjZDE1ZDZMGYwMGEwOA==';
$curl->setCookie($cookie_name, $cookie_value);
```
or
```php
$cookies = [
    'XSRF-TOKEN' => 'OWY4NmQwODE4ODRMjJjZDE1ZDZMGYwMGEwOA==',
    'ci_session' => 'esa2tb3mviicp2cb5abz32g'
];
$curl->setCookie($cookies);
```
---

### setCookieFile()
It takes the cookie data from the file with the cookie information in Netscape format and adapts it to the request.

```php
$curl->setCookieFile(__DIR__.DIRECTORY_SEPARATOR.'cookies.txt');
```
---

### setCookieJar()
It adds and saves the cookie information in Netscape format to the specified file.

```php
$curl->setCookieJar(__DIR__.DIRECTORY_SEPARATOR.'cookies.txt');
```
---

### setFollow()
Used to allow if the request is redirecting. The default ``false`` is off.

```php
$curl->setFollow(true);
```
---

### setReturn()
Allows the request transfer to be output or not. The default ``false`` is off.

```php
$curl->setReturn(true);
```
---

### setReferer()
Sets the ``Referer`` header to use in the request.

```php
$curl->setReferer('https://httpbin.org/');
```
---

### setAutoReferer()
If the request has redirected, it automatically sets the ``Referer`` header to be used in the request. The default ``false`` is off.

```php
$curl->setAutoReferer(true);
```
---

### setTimeout()
Sets the timeout of curl functions in seconds.

```php
$curl->setTimeout(5);
```
---

### setConnectTimeout()
Sets the attempt time, in seconds, of the request.

```php
$curl->setConnectTimeout(5);
```
---

### setMaxConnect()
Sets the maximum number of simultaneous connections.

```php
$curl->setMaxConnect(5);
```
---

### setMaxRedirect()
Sets the maximum number of redirects. The default value is ``20``.

```php
$curl->setMaxRedirect(5);
```
---

### setProxy()
It allows the request to be connected via proxy. It can auto parse. Only the 1st argument should be used when auto-parsing. It uses the default ``HTTPS`` proxy type.

```php
$curl->setProxy('127.0.0.1', '8080');
```
veya (oto ayrıştılır)
```php
$curl->setProxy('127.0.0.1:8080');
```
or proxy type can be specified. (auto parse)
```php
$curl->setProxy('http://127.0.0.1:8080');
$curl->setProxy('https://127.0.0.1:8080');
$curl->setProxy('socks4://127.0.0.1:8080');
$curl->setProxy('socks5://127.0.0.1:8080');
```
or authentication with username and password. (auto parse)
```php
$curl->setProxy('username:password@127.0.0.1:8080');
$curl->setProxy('http://username:password@127.0.0.1:8080');
$curl->setProxy('https://username:password@127.0.0.1:8080');
$curl->setProxy('socks4://username:password@127.0.0.1:8080');
$curl->setProxy('socks5://username:password@127.0.0.1:8080');
```
---

### setProxyType()
It determines the proxy type while allowing the request to be connected via proxy. Curl constants must be used.

```php
$curl->setProxyType(CURLPROXY_HTTPS);
```
---

### setProxyAuth()
It sets the proxy authentication information while allowing the request to connect via proxy.

```php
$curl->setProxyAuth('user:pass');
```
or
```php
$curl->setProxyAuth('user', 'pass');
```
or
```php
$curl->setProxyAuth('user');
```
---

### send()
It is the function where requests are made. It is used to implement request methods. All request methods in the class send requests using this method.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->send('GET', 'https://httpbin.org/get', $headers);
```
or
```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->send('POST', 'https://httpbin.org/get', $headers, $body, $curl::JSON);
```
---

### get()
Sends a request using the ``GET`` request method. This request does not accept the request body.
```php
$curl->get('https://httpbin.org/get');
```
veya
```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->get('https://httpbin.org/get', $headers);
```
veya
```php
$headers = 'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html';

$curl->get('https://httpbin.org/get', $headers);
```
---

### post()
It sends a request using the ``POST`` request method. This request accepts the request body. The setBody method is valid for all request methods that can be sent body.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->post('https://httpbin.org/post', $headers, $body);
$curl->post('https://httpbin.org/post', $headers, $body, $curl::JSON);
```
---

### put()
Throws a request using the ``PUT`` request method. This request accepts the request body. The setBody method is valid for all request methods that can be sent body.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->put('https://httpbin.org/put', $headers, $body);
$curl->put('https://httpbin.org/put', $headers, $body, $curl::JSON);
```
---

### put()
It sends a request using the ``PUT`` request method. This request accepts the request body. The setBody method is valid for all request methods that can be sent body.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->put('https://httpbin.org/put', $headers, $body);
$curl->put('https://httpbin.org/put', $headers, $body, $curl::JSON);
```
---

### delete()
It assigns a request using the ``DELETE`` request method. This request accepts the request body. The setBody method is valid for all request methods that can be sent body.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->delete('https://httpbin.org/delete', $headers, $body);
$curl->delete('https://httpbin.org/delete', $headers, $body, $curl::JSON);
```
---

### patch()
It assigns a request using the ``PATCH`` request method. This request accepts the request body. The setBody method is valid for all request methods that can send a body.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->patch('https://httpbin.org/patch', $headers, $body);
$curl->patch('https://httpbin.org/patch', $headers, $body, $curl::JSON);
```
---

### head()
It assigns a request using the ``HEAD`` request method. This request does not accept body and does not return a response.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->head('https://httpbin.org/head', $headers);
```
---

### connect()
It assigns a request using the ``CONNECT`` request method. This request does not accept the request body.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->connect('https://httpbin.org/connect', $headers);
```
---

### options()
It assigns a request using the ``OPTIONS`` request method. This request does not accept the request body.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->options('https://httpbin.org/options', $headers);
```
---

### trace()
It assigns a request using the ``TRACE`` request method. This request does not accept body and does not return a response.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->trace('https://httpbin.org/options', $headers);
```
---

### exec()
Running this method is mandatory after setting up any request. The request is not sent until the ``exec``` method is appended to the end of the request. This class works with the logic in the curl library. The reason for necessity is to make it easier to check the settings without sending the request.

```php
$curl->exec();
```
> for setting check, print $curl variable with print_r method without adding exec.
---

## Details of Response Methods

### getInfo()
It is a method that returns detailed information about curl that occurs after sending a curl request.

All information can be directly returned as array type.
```php
$curl->getInfo();

/*
Array
(
    [url] => https://httpbin.org/get
    [content_type] => application/json
    [http_code] => 200
    [header_size] => 202
    [request_size] => 51
    [filetime] => -1
    [ssl_verify_result] => 0
    [redirect_count] => 0
    [total_time] => 0.725079
    [namelookup_time] => 0.126243
    [connect_time] => 0.271622
    [pretransfer_time] => 0.577915
    [size_upload] => 0
    [size_download] => 221
    [speed_download] => 304
    [speed_upload] => 0
    [download_content_length] => 221
    [upload_content_length] => -1
    [starttransfer_time] => 0.724962
    [redirect_time] => 0
    [redirect_url] => 
    [primary_ip] => 127.0.0.1
    [certinfo] => Array
        (
        )

    [primary_port] => 80
    [local_ip] => 127.0.0.1
    [local_port] => 22219
    [http_version] => 3
    [protocol] => 2
    [ssl_verifyresult] => 0
    [scheme] => HTTPS
    [appconnect_time_us] => 577763
    [connect_time_us] => 271622
    [namelookup_time_us] => 126243
    [pretransfer_time_us] => 577915
    [redirect_time_us] => 0
    [starttransfer_time_us] => 724962
    [total_time_us] => 725079
)
*/
```
or it can also return singular information.
```php
$curl->getInfo('http_code');

```
---

### getCurlError()
If a curl-based error occurs after sending the request, this method returns the error.

```php
$curl->getCurlError();
```
---

### getResponse()
Returns the response from the request.

```php
$curl->getResponse();
```
or you can return it by deleting the extra spaces in the answer.
```php
$curl->getResponse(true);
```
---

### getRespJson()
If the response received from the request is in json format, it returns the json data by parsing it. The return type is object. Array type can also be set.

```php
$curl->getRespJson();
```
or return json data as array.
```php
$curl->getRespJson(true);
```
Can set flag for ``json_decode`` with 2nd argument.
```php
$curl->getRespJson(false, JSON_PRETTY_PRINT);
```
---

### getEffective()
If the request has a redirect, it returns the last source visited. For this, the setReturn method must be set to true.

```php
$curl->getEffective();

// https://httpbin.org/get
```
---

### getHttpCode()
Returns the http status code of the request.

```php
$curl->getHttpCode();

// 200
```
---

### getHeader()
Returns any header returned from the request. Returns a singular value. The header id is added to the second argument. The header id argument returns data from the headers of the default last request.

```php
$curl->getHeader('device_id');

// e324h4e708f097febb40384a51as48
```
or if there is a redirect, header can also be taken from the previous request. The header id is entered in the second argument.
```php
$curl->getHeader('device_id', 0);

// e324h4e708f097febb40384a51as48
```
---

### getHeaders()
Returns all headers returned from the request. Array returns value. The header id is added to the first argument. The header id argument returns data from the headers of the default last request.

```php
$curl->getHeaders();

/*
Array
(
    [response_code] => 200
    [Date] => Sat, 21 May 2022 10:00:34 GMT
    [Content-Type] => application/json
    [Content-Length] => 291
    [Connection] => keep-alive
    [Server] => gunicorn/19.9.0
    [Access-Control-Allow-Origin] => *
    [Access-Control-Allow-Credentials] => true
)
*/
```
or
```php
$curl->getHeaders(0);
```
---

### getCookie()
Returns the specified cookies returned from the request response. The header id is valid in the 2nd argument.

```php
$curl->getCookie('XSRF-TOKEN');

// OWY4NmQwODE4ODRjN2Q2NTlhMmZlYWEwYzU1YWQwMTVhM2JmNGYxYjJiMGI4MjJjZDE1ZDZMGYwMGEwOA==
```
or
```php
$curl->getCookie('XSRF-TOKEN', 0);

// OWY4NmQwODE4ODRjN2Q2NTlhMmZlYWEwYzU1YWQwMTVhM2JmNGYxYjJiMGI4MjJjZDE1ZDZMGYwMGEwOA==
```
---

### getCookiesRaw()
Returns the cookies returned from the request in cookie format. header id is valid in 1st argument.

```php
$curl->getCookiesRaw();

// laravel_session=eyJpdiI6InV5bGRQNFJ4c01TYjZwT0I0amxzS1E9PSIsInZhbHVlIjoiZFI2WWpVWGxmTldDcVJvVlwvbVJicXBxM0pjRkVRUlBRKzZWb1BkbzliZHBVdTlmUEV4UzZkaFVMbmlRTHNYczFOZm5HSWkwRXhjb3BJRGI1NGRyM2tnPT0iLCJtYWMiOiJjMjAwMWIyMGIxYmQwYzkxMGQyNGJhMDZmZDJiNThjNGZhMTUyZWVjZDlkNjg5ZWVjYjY2MGE1ZTlmZDAxOGNmIn0=
```
or
```php
$curl->getCookiesRaw(0);

// laravel_session=eyJpdiI6InV5bGRQNFJ4c01TYjZwT0I0amxzS1E9PSIsInZhbHVlIjoiZFI2WWpVWGxmTldDcVJvVlwvbVJicXBxM0pjRkVRUlBRKzZWb1BkbzliZHBVdTlmUEV4UzZkaFVMbmlRTHNYczFOZm5HSWkwRXhjb3BJRGI1NGRyM2tnPT0iLCJtYWMiOiJjMjAwMWIyMGIxYmQwYzkxMGQyNGJhMDZmZDJiNThjNGZhMTUyZWVjZDlkNjg5ZWVjYjY2MGE1ZTlmZDAxOGNmIn0=
```
---

### getCookiesArray()
Returns the cookies returned from the request. The header id is valid in the 1st argument. As the name suggests, the returned data type is array.

```php
$curl->getCookiesArray();

/*
Array
(
    [laravel_session] => eyJpdiI6InV5bGRQNFJ4c01TYjZwT0I0amxzS1E9PSIsInZhbHVlIjoiZFI2WWpVWGxmTldDcVJvVlwvbVJicXBxM0pjRkVRUlBRKzZWb1BkbzliZHBVdTlmUEV4UzZkaFVMbmlRTHNYczFOZm5HSWkwRXhjb3BJRGI1NGRyM2tnPT0iLCJtYWMiOiJjMjAwMWIyMGIxYmQwYzkxMGQyNGJhMDZmZDJiNThjNGZhMTUyZWVjZDlkNjg5ZWVjYjY2MGE1ZTlmZDAxOGNmIn0=
)
*/
```
or
```php
$curl->getCookiesArray(0);

/*
Array
(
    [laravel_session] => eyJpdiI6InV5bGRQNFJ4c01TYjZwT0I0amxzS1E9PSIsInZhbHVlIjoiZFI2WWpVWGxmTldDcVJvVlwvbVJicXBxM0pjRkVRUlBRKzZWb1BkbzliZHBVdTlmUEV4UzZkaFVMbmlRTHNYczFOZm5HSWkwRXhjb3BJRGI1NGRyM2tnPT0iLCJtYWMiOiJjMjAwMWIyMGIxYmQwYzkxMGQyNGJhMDZmZDJiNThjNGZhMTUyZWVjZDlkNjg5ZWVjYjY2MGE1ZTlmZDAxOGNmIn0=
)
*/
```
---

### getBetween()
Finds and returns text in the specified range from the response received from the request. Returns singular string data.

```php

// <p>asd</p>

$curl->getBetween('<p>', '</p>');

//asd
```
The 3rd argument is equal to the 1st argument of the ``getResponse`` method. It deletes the extra spaces in the response.
```php
$curl->getBetween('<p>', '</p>', true);
```
---

### getBetweens()
Finds and returns text in the specified range from the response received from the request. Returns multiple array data. Gets all of the specified ranges if more than one.

```php
/*
<p>test</p>
<p>test 2</p>
*/

$curl->getBetweens('<p>', '</p>');
/*
Array
(
    [0] => test
    [1] => test 2
)
*/
```
The 3rd argument is equal to the 1st argument of the ``getResponse ``method. It deletes the extra spaces in the response.
```php
// <p>test</p><p>test 2</p>
$curl->getBetweens('<p>', '</p>', true);
/*
Array
(
    [0] => test
    [1] => test 2
)
*/
```
---

## Details of Other Methods

### find()
It performs a text search in the response of the request, and returns an object type result if any. You can search for singular information as well as plural. The default source is the data retrieved from the ``getResponse`` method. Different source can be added to the optional 2nd argument.

title tag check
```php
$find = $curl->find("<title>Homepage</title>");

if ($find->result) {
    echo 'Bulunan: '.$find->finded;
}

/*
stdClass Object
(
    [result] => 1
    [finded] => <title>Anasayfa</title>
)
*/
```
or multiple searches.
```php
$found = [
    "<title>Homepage</title>",
    "Homepage",
    "Topic is added.",

];
$find = $curl->find($found);

if ($find->result) {
    echo 'Founds: '; print_r($find->finded);
    
}

/*
stdClass Object
(
    [result] => 1
    [finded] => Array
        (
            [0] => <title>Homepage</title>
            [1] => Homepage
        )

)
*/
```

or a different source can be added.
```php
$found = [
    "<title>Homepage</title>",
    "Homepage",
    "Topic is added.",

];

$string = "<title>Homepage</title>Topic is added.";

$find = $curl->find($found, $string);

if ($find->result) {
    echo 'Found: '; print_r($find->finded);
    
}

/*
stdClass Object
(
    [result] => 1
    [finded] => Array
        (
            [0] => <title>Homepage</title>
            [1] => Homepage
        )

)
*/
```
---

### getOpt()
Returns the values ​​of constants set with the ``setOpt`` method. Names do not contain the names of constants. This is how the curl library offers.

```php
$curl->getOpt();

/*
Array
(
    [42] => 1
    [19913] => 1
    [10036] => GET
    [10002] => https://www.google.com/
    [80] => 1
    [84] => 2
)
*/
```
or
```php
$curl->getOpt(CURLOPT_URL);

// https://www.google.com/
```
---

## Details of Constants

### RAW
It allows it to detect the body data to be sent in the request as plain text.

```php
$curl::RAW;
```
---

### JSON
It makes it detect the body data to be sent in the request as json and converts it to json data.

```php
$curl::JSON;
```
---

### QUERY
It makes it detect the body data to be sent in the request as a form and converts it to form data.

```php
$curl::QUERY;
```
---