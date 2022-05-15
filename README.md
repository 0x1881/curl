# Curl Repo
Bot yapımı için test amaçlı kodlanmış bir curl sınıfıdır.

# Kurulum
```
composer require 0x1881/curl:dev-main
```

# Kullanımı
```php
<?php

require_once 'curl.php';

$curl = new \C4N\Curl;
$curl->get('https://httpbin.org/get');
$curl->exec();

print_r($curl);

echo $curl->getResponse();
```