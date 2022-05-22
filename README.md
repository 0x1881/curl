# PHP Curl
Bot yapımı için kodlanmış bir curl sınıfıdır.
--- 

## Kurulum
```ps1
composer require 0x1881/curl
```
--- 

## Örnek Kullanım
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

## Metodlar

### [İstek Metodları](#İstek-Metodlarının-Detayları)
- [setDefault](#setdefault)
- [setMethod](#setmethod)
- [setUrl](#seturl)
- [setHeader](#setheader)
- [setBody](#setbody)
- [setOpt](#setOpt)
- [setDebug](#setDebug)
- [setUserAgent](#setUserAgent)
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

### [Cevap Metodları](#Cevap-Metodlarının-Detayları)
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

### [Diğer Metodlar](#Diğer-Metodların-Detayları)
- [find](#find)
- [getOpt](#getOpt)

### [Sabitler](#Sabitlerin-Detayları)
- [RAW](#RAW)
- [JSON](#JSON)
- [QUERY](#QUERY)
--- 

# Metodların Ayrıntılı Açıklaması

## İstek Metodlarının Detayları

### setDefault()
Sınıfı varsayılan istek ayarlarına döndürür. Bu sayede her yeni atılan istekte ayarlar tekrar varsayılana dönecek şekilde düzenlenir.

```php
$curl->setDefault();
```
---

### setMethod()
İsteğin türünü belirtmek için kullanılır.

#### Kabul edilen türler

- ```GET```, ```POST```, ```PUT```, ```DELETE```, ```PATCH```, ```HEAD```, ```CONNECT```, ```OPTIONS```, ```TRACE```

```php
$curl->setMethod('GET');
```
---

### setUrl()
İsteğin hedefini belirlersiniz.

```php
$curl->setUrl('https://httpbin.org/get');
```
---

### setHeader()
Gönderilecek isteğe header ekler.

```php
$curl->setHeader('Test-Header: value');
```
veya
```php
$curl->setHeader('Test-Header', 'value');
```
veya
```php
$headers = [
    'Test-Header: value',
    'Test-Header2: value'
];
$curl->setHeader($headers);
```
---

### setBody()
Gönderilecek isteğe body ekler. Belirli istek metodları için geçerlidir.

**Raw**: Belirtilen body direkt düz metin olarak istek için ayarlanır.
```php
$curl->setBody('name=Mehmet&lastname=Can');
```
**Query 1**: ```x-www-form-urlencoded``` içerik türünde bir body ayarlar. Örnek yorum olarak eklenmiştir. Eğer veri array türde ise metod onu belirttiğimiz formata dönüştürecektir.
```php
$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->setBody($body);

// name=Mehmet&lastname=Can
```
**Query 2**: ```x-www-form-urlencoded``` içerik türünde bir body ayarlar. Örnek yorum olarak eklenmiştir.
```php
$body = [
    'name' => 'Mehmet',
    'lastname' => 'Can'
];

$curl->setBody($body, $curl::QUERY);

// name=Mehmet&lastname=Can
```
**_Json_**: ```json``` formatında bir body ayarlar. Örnek yorum olarak eklenmiştir.
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
Curl kütüphasinin curl_setopt metodunu pratikleştiren metoddur. İsteğe göre Curlopt sabitleri kullanılabilir.

```php
$curl->setOpt(CURLOPT_URL, 'https://httpbin.org/get');
```
---

### setDebug()
Curl kütüphasinden CURLOPT_VERBOSE sabitini true olarak ayarlar bu sayede terminal satırlarında isteğe ait debug verilerini döndürür. Varsayılan ```false``` kapalıdır.

```php
$curl->setDebug(true);
```
---

### setUserAgent()
İsteğin user-agent başlığına istenen ua bilgisini ekler.

```php
$curl->setUserAgent('Googlebot/2.1 (+http://www.google.com/bot.html)');
```
---

### setCookieFile()
Netscape formatındaki cookie bilgileri eklenmiş dosyadan, cookie verilerini alıp isteğer uyarlar.

```php
$curl->setCookieFile(__DIR__.DIRECTORY_SEPARATOR.'cookies.txt');
```
---

### setCookieJar()
Netscape formatındaki cookie bilgilerini belirtilmiş dosyaya ekler ve kaydeder.

```php
$curl->setCookieJar(__DIR__.DIRECTORY_SEPARATOR.'cookies.txt');
```
---

### setFollow()
İsteğin yönlendirme yapıyorsa izin vermek için kullanılır. Varsayılan ```false``` kapalıdır.

```php
$curl->setFollow(true);
```
---

### setReturn()
İstek aktarımını çıktı edilmesini ya da edilmemesini sağlar. Varsayılan ```false``` kapalıdır.

```php
$curl->setReturn(true);
```
---

### setReferer()
İstekte kullanılacak "Referer: " header içeriğini ayarlar.

```php
$curl->setReferer('https://httpbin.org/');
```
---

### setAutoReferer()
İstek yönlendirme yapmışsa istekte kullanılacak "Referer: " header içeriğini otomatik ayarlar. Varsayılan ```false``` kapalıdır.

```php
$curl->setAutoReferer(true);
```
---

### setTimeout()
Curl işlevlerinin zaman aşımını saniye cinsinden ayarlar.

```php
$curl->setTimeout(5);
```
---

### setConnectTimeout()
İsteğin saniye cinsinden denenme süresini ayarlar.

```php
$curl->setConnectTimeout(5);
```
---

### setMaxConnect()
Maksimum eş zamanlı bağlantı sayısını ayarlar.

```php
$curl->setMaxConnect(5);
```
---

### setMaxRedirect()
Maksimum yönlendirme sayısını ayarlar. Varsayılan değer ```20```.

```php
$curl->setMaxRedirect(5);
```
---

### setProxy()
İsteğin proxy aracılığı ile bağlanmasını sağlar. Oto ayrıştırma yapabilir. Oto ayrıştırma yapılırken sadece 1. argüman kullanılmalıdır. Varsayılan ```HTTPS``` proxy türü kullanır.

```php
$curl->setProxy('127.0.0.1', '8080');
```
veya (oto ayrıştılır)
```php
$curl->setProxy('127.0.0.1:8080');
```
veya proxy türü belirtilebilir. (oto ayrıştılır)
```php
$curl->setProxy('http://127.0.0.1:8080');
$curl->setProxy('https://127.0.0.1:8080');
$curl->setProxy('socks4://127.0.0.1:8080');
$curl->setProxy('socks5://127.0.0.1:8080');
```
veya kullanıcı adı ve şifre ile kimlik doğrulaması yapılabilir. (oto ayrıştılır)
```php
$curl->setProxy('username:password@127.0.0.1:8080');
$curl->setProxy('http://username:password@127.0.0.1:8080');
$curl->setProxy('https://username:password@127.0.0.1:8080');
$curl->setProxy('socks4://username:password@127.0.0.1:8080');
$curl->setProxy('socks5://username:password@127.0.0.1:8080');
```
---

### setProxyType()
İsteğin proxy aracılığı ile bağlanmasını sağlarken proxy türünü belirler. Curl sabitleri kullanılmalıdır.

```php
$curl->setProxyType(CURLPROXY_HTTPS);
```
---

### setProxyAuth()
İsteğin proxy aracılığı ile bağlanmasını sağlarken proxy kimlik doğrulama bilgilerini ayarlar.

```php
$curl->setProxyAuth('user:pass');
```
veya
```php
$curl->setProxyAuth('user', 'pass');
```
veya
```php
$curl->setProxyAuth('user');
```
---

### send()
İsteklerin yapıldığı fonksiyondur. İstek metodlarını pratikleştirmek için kullanılır. Sınıftaki tüm istek metodları send metodunu kullanarak istek göndermektedir.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->send('GET', 'https://httpbin.org/get', $headers);
```
veya
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
```GET``` istek metodunu kullanarak istek atar. Bu istek body kabul etmez.
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
```POST``` istek metodunu kullanarak istek atar. Bu istek body kabul eder. setBody metodu body gönderilebilen tüm istek metodlarında geçerlidir.

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
```PUT``` istek metodunu kullanarak istek atar. Bu istek body kabul eder. setBody metodu body gönderilebilen tüm istek metodlarında geçerlidir.

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
```PUT``` istek metodunu kullanarak istek atar. Bu istek body kabul eder. setBody metodu body gönderilebilen tüm istek metodlarında geçerlidir.

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
```DELETE``` istek metodunu kullanarak istek atar. Bu istek body kabul eder. setBody metodu body gönderilebilen tüm istek metodlarında geçerlidir.

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
```PATCH``` istek metodunu kullanarak istek atar. Bu istek body kabul eder. setBody metodu body gönderilebilen tüm istek metodlarında geçerlidir.

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
```HEAD``` istek metodunu kullanarak istek atar. Bu istek body kabul etmez ve response döndürmez.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->head('https://httpbin.org/head', $headers);
```
---

### connect()
```CONNECT``` istek metodunu kullanarak istek atar. Bu istek body kabul etmez.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->connect('https://httpbin.org/connect', $headers);
```
---

### options()
```OPTIONS``` istek metodunu kullanarak istek atar. Bu istek body kabul etmez.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->options('https://httpbin.org/options', $headers);
```
---

### trace()
```TRACE``` istek metodunu kullanarak istek atar. Bu istek body kabul etmez ve response döndürmez.

```php
$headers = [
    'User-agent: Googlebot/2.1 (+http://www.google.com/bot.html)'
];

$curl->trace('https://httpbin.org/options', $headers);
```
---

### exec()
Bu metodun çalışması herhangi bir isteğin ayarları yapıldıktan sonra  zorunludur. ```exec``` metodu en sona eklenmeden istek gönderilmez. Curl kütüphanesindeki mantıkla çalışmaktadır. Zorunluluk sebebi, isteği göndermeden ayarların doğru olup olmadığını daha rahat kontrol etmek içindir.

```php
$curl->exec();
```
> ayar kontrolü için exec eklemeden $curl değişkenini print_r metodu ile yazdırın.
---

## Cevap Metodlarının Detayları

### getInfo()
Curl isteği gönderildikten sonra oluşan curl taraflı oluşan detaylı bilgileri döndüren metoddur.

Tüm bilgiler array türünde direkt döndürülebilir.
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
veya tek bir bilgi de döndürülebilir.
```php
$curl->getInfo('http_code');

```
---

### getCurlError()
İstek gönderildikten sonra curl bazlı bir hata oluşursa bu metod hatayı döndürmektedir.

```php
$curl->getCurlError();
```
---

### getResponse()
İstekten alınan cevabı bize döndürür.

```php
$curl->getResponse();
```
veya cevaptaki fazla boşlukları silerek döndürebilirsiniz.
```php
$curl->getResponse(true);
```
---

### getRespJson()
İstekten alınan cevap eğer json formatında ise bize json verisini ayrıştırarak döndürür. Dönüş türü objedir. Array türü de ayarlanabilir.

```php
$curl->getRespJson();
```
veya json verisini array olarak döndürün.
```php
$curl->getRespJson(true);
```
2. argümanla json_decode için flag ayarlanabilir
```php
$curl->getRespJson(false, JSON_PRETTY_PRINT);
```
---

### getEffective()
İstekte yönlendirme varsa son gidilen kaynağı bize döndürür. Bunun için setReturn metodu true ayarlanmalıdır.

```php
$curl->getEffective();

// https://httpbin.org/get
```
---

### getHttpCode()
İsteğin http durum kodunu döndürür.

```php
$curl->getHttpCode();

// 200
```
---

### getHeader()
İstekten dönen herhangi bir header döndürür. Tekil değer verir. İkinci argümana header idsi eklenir. header id argümanı varsayılan son isteğin headerlarından veri döndürür.

```php
$curl->getHeader('device_id');

// e324h4e708f097febb40384a51as48
```
veya yönlendirme varsa önceki istekten header da alınabilir. 2. argümana header idsi girilir.
```php
$curl->getHeader('device_id', 0);

// e324h4e708f097febb40384a51as48
```
---

### getHeaders()
İstekten dönen tüm headerları döndürür. Array değer döndürür. İlk argümana header idsi eklenir. header id argümanı varsayılan son isteğin headerlarından veri döndürür.

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
veya
```php
$curl->getHeaders(0);
```
---

### getCookie()
İstekten dönen cookilerden seçilmiş olanı döndürür. 2. argümanda header id geçerlidir.

```php
$curl->getCookie('XSRF-TOKEN');

// OWY4NmQwODE4ODRjN2Q2NTlhMmZlYWEwYzU1YWQwMTVhM2JmNGYxYjJiMGI4MjJjZDE1ZDZMGYwMGEwOA==
```
veya
```php
$curl->getCookie('XSRF-TOKEN', 0);

// OWY4NmQwODE4ODRjN2Q2NTlhMmZlYWEwYzU1YWQwMTVhM2JmNGYxYjJiMGI4MjJjZDE1ZDZMGYwMGEwOA==
```
---

### getCookiesRaw()
İstekten dönen cookileri cookie formatında döndürür. 1. argümanda header id geçerlidir

```php
$curl->getCookiesRaw();

// laravel_session=eyJpdiI6InV5bGRQNFJ4c01TYjZwT0I0amxzS1E9PSIsInZhbHVlIjoiZFI2WWpVWGxmTldDcVJvVlwvbVJicXBxM0pjRkVRUlBRKzZWb1BkbzliZHBVdTlmUEV4UzZkaFVMbmlRTHNYczFOZm5HSWkwRXhjb3BJRGI1NGRyM2tnPT0iLCJtYWMiOiJjMjAwMWIyMGIxYmQwYzkxMGQyNGJhMDZmZDJiNThjNGZhMTUyZWVjZDlkNjg5ZWVjYjY2MGE1ZTlmZDAxOGNmIn0=
```
veya
```php
$curl->getCookiesRaw(0);

// laravel_session=eyJpdiI6InV5bGRQNFJ4c01TYjZwT0I0amxzS1E9PSIsInZhbHVlIjoiZFI2WWpVWGxmTldDcVJvVlwvbVJicXBxM0pjRkVRUlBRKzZWb1BkbzliZHBVdTlmUEV4UzZkaFVMbmlRTHNYczFOZm5HSWkwRXhjb3BJRGI1NGRyM2tnPT0iLCJtYWMiOiJjMjAwMWIyMGIxYmQwYzkxMGQyNGJhMDZmZDJiNThjNGZhMTUyZWVjZDlkNjg5ZWVjYjY2MGE1ZTlmZDAxOGNmIn0=
```
---

### getCookiesArray()
İstekten dönen cookileri döndürür. 1. argümanda header id geçerlidir. Adından da anlaşılacağ gibi dönen veri türü arraydir.

```php
$curl->getCookiesArray();

/*
Array
(
    [laravel_session] => eyJpdiI6InV5bGRQNFJ4c01TYjZwT0I0amxzS1E9PSIsInZhbHVlIjoiZFI2WWpVWGxmTldDcVJvVlwvbVJicXBxM0pjRkVRUlBRKzZWb1BkbzliZHBVdTlmUEV4UzZkaFVMbmlRTHNYczFOZm5HSWkwRXhjb3BJRGI1NGRyM2tnPT0iLCJtYWMiOiJjMjAwMWIyMGIxYmQwYzkxMGQyNGJhMDZmZDJiNThjNGZhMTUyZWVjZDlkNjg5ZWVjYjY2MGE1ZTlmZDAxOGNmIn0=
)
*/
```
veya
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
İstekten alınan cevaptan, belirtilen aralıktaki metni bulup döndürür. Tekil değerinde string veri döndürür.

```php

// <p>asd</p>

$curl->getBetween('<p>', '</p>');

//asd
```
3. argüman getResponse metodunun 1. argümanına eşittir. Cevaptaki fazla boşlukları siler.
```php
$curl->getBetween('<p>', '</p>', true);
```
---

### getBetweens()
İstekten alınan cevaptan, belirtilen aralıktaki metni bulup döndürür. Çoğul değerinde array veri döndürür. Belirtilen aralıklar birden fazla hepsini alır.

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
3. argüman getResponse metodunun 1. argümanına eşittir. Cevaptaki fazla boşlukları siler.
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

## Diğer Metodların Detayları

### find()
İsteğin cevabında bir metin araması yapar varsa obje türünde sonuç döndürür. Tekil bilgi arama yapılabildiği gibi çoğul da yapılabilir. Varsayılan kaynak ```getResponse``` metodundan alınan veridir. İsteğe bağlı 2. argümana farklı kaynak eklenebilir.

title tagı kontrolü
```php
$find = $curl->find("<title>Anasayfa</title>");

if ($find->result) {
    echo 'Bulundu'.PHP_EOL;
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
veya çoklu arama yapabilir.
```php
$aranacaklar = [
    "<title>Anasayfa</title>",
    "Anasayfa",
    "Konu eklendi.",

];
$find = $curl->find($aranacaklar);

if ($find->result) {
    echo 'Bulundu'.PHP_EOL;
    echo 'Bulunanlar: '; print_r($find->finded);
    
}

/*
stdClass Object
(
    [result] => 1
    [finded] => Array
        (
            [0] => <title>Anasayfa</title>
            [1] => Anasayfa
        )

)
*/
```

veya farklı kaynak eklenebilir.
```php
$aranacaklar = [
    "<title>Anasayfa</title>",
    "Anasayfa",
    "Konu eklendi.",

];

$metin = "<title>Anasayfa</title>Konu eklendi.";

$find = $curl->find($aranacaklar, $metin);

if ($find->result) {
    echo 'Bulundu'.PHP_EOL;
    echo 'Bulunanlar: '; print_r($find->finded);
    
}

/*
stdClass Object
(
    [result] => 1
    [finded] => Array
        (
            [0] => <title>Anasayfa</title>
            [1] => Anasayfa
        )

)
*/
```
---

### getOpt()
 ```setOpt``` metoduyla ayarlanan sabitlerin değerlerini döndürür. Nameler sabitlerin adını içermez. Curl kütüphanesi böyle sunmaktadır.

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
veya
```php
$curl->getOpt(CURLOPT_URL);

// https://www.google.com/
```
---

## Sabitlerin Detayları

### RAW
İstekte gönderilcek body verisini düz metin olarak algılamasını sağlar.

```php
$curl::RAW;
```
---

### JSON
İstekte gönderilcek body verisini json olarak algılamasını sağlar ve json verisine dönüştürür.

```php
$curl::JSON;
```
---

### QUERY
İstekte gönderilcek body verisini form olarak algılamasını sağlar ve form verisine dönüştürür.

```php
$curl::QUERY;
```
---