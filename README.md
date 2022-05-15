# Curl Repo

Bot yapımı için test amaçlı kodlanmış bir curl sınıfıdır.

# Kullanımı

```php
<?php

require_once 'curl.php';

$curl = new \C4N\Curl;
$curl->get('https://httpbin.org/get');

print_r($curl);

echo $curl->getResponse();
```