<?php

namespace C4N;

use ReflectionClass;

class C4NException extends \Exception
{
    public function __construct($message = 'Something is wrong.', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Curl class
 * 
 * @package C4N
 * @author Mehmet Can
 */
class Curl
{
    public const QUERY = 'http_build_query';
    public const JSON = 'json_encode';
    public const RAW = 'strval';
    public const PROXY_REGEX = '/^(?:(?<method>[http|https|socks4|socks5]*?):\/\/)?(?:(?<username>\w+)(?::(?<password>\w*))@)?(?<host>(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}|((?:\d{1,3})(?:\.\d{1,3}){3}))(?::(?<port>\d{1,5}))$/ms';

    private static $method_properties = [
        'GET' => [
            'req_body' => false,
            'res_body' => true,
        ],
        'POST' => [
            'req_body' => true,
            'res_body' => true,
        ],
        'PUT' => [
            'req_body' => true,
            'res_body' => true,
        ],
        'DELETE' => [
            'req_body' => true,
            'res_body' => true, //optional
        ],
        'PATCH' => [
            'req_body' => true,
            'res_body' => true,
        ],
        'HEAD' => [
            'req_body' => false,
            'res_body' => false,
        ],
        'CONNECT' => [
            'req_body' => false,
            'res_body' => true,
        ],
        'OPTIONS' => [
            'req_body' => false,
            'res_body' => true,
        ],
        'TRACE' => [
            'req_body' => false,
            'res_body' => false,
        ],
    ];
    public $req = null;
    public $res = null;

    /**
     * @return void 
     * @throws C4NException 
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            $this->setError('Library is not loaded', 'cURL');
        }
        $this->setDefault();
    }

    protected static function getConstName($value)
    {
        $class = new ReflectionClass(__CLASS__);
        $constants = array_flip($class->getConstants());

        return $constants[$value];
    }

    /**
     * Set error exception
     * 
     * @param mixed $message 
     * @param mixed $value 
     * @return void 
     * @throws C4NException 
     */
    protected function setError($message, $value = null): void
    {
        throw new C4NException($message . ": " . $value);
    }

    /**
     * Get curl error
     * 
     * @return mixed 
     */
    public function getCurlError()
    {
        return isset($this->res->error) ?: $this->res->error;
    }

    /**
     * Request default options set function
     * 
     * @return void 
     */
    public function setDefault()
    {
        $this->req = new \stdClass();
        $this->req->ch = curl_init();
        $this->res = new \stdClass();
        $this->setOpt(\CURLOPT_HEADER, true);
        $this->setOpt(\CURLOPT_RETURNTRANSFER, true);
        return $this;
    }

    /**
     * Request method set function
     * 
     * @param string $method 
     * @return $this
     * @throws C4NException 
     */
    public function setMethod(string $method)
    {
        $this->setDefault();
        $methodUP = strtoupper($method);
        if (isset(self::$method_properties[$methodUP])) {
            $this->req->method = $methodUP;
            $this->setOpt(\CURLOPT_CUSTOMREQUEST, $this->req->method);
        } else {
            $this->setError("The request method invalid", $method);
        }

        return $this;
    }

    /**
     * Request url set function
     * 
     * @param string $url 
     * @return $this 
     */
    public function setUrl(string $url)
    {
        $this->req->url = $url;
        $this->setOpt(\CURLOPT_URL, $this->req->url);

        return $this;
    }


    /**
     * Request header set function
     * 
     * @param mixed|null $header 
     * @param string $value 
     * @return $this 
     * @throws C4NException 
     */
    public function setHeader($header = null, string $value = "curl_no_value")
    {
        if ((\is_array($header))) {
            $new_header = null;
            foreach ($header as $key => $value) {
                if (\is_string($key) && !\is_array($value)) {
                    $new_header[] = $key . ': ' . $value;
                } else {
                    $new_header[] = $value;
                }
            }
            if (!\is_null($new_header)) {
                $header = $new_header;
            }
            if (isset($this->req->headers)) {
                $this->req->headers = array_merge($this->req->headers, $header);
            } else {
                $this->req->headers = $header;
            }
        } elseif (\is_string($header)) {
            $new_header = $value == 'curl_no_value' ? [$header] : [$header . ': ' . $value];
            if (isset($this->req->headers)) {
                $this->req->headers = array_merge($this->req->headers, $new_header);
            } else {
                $this->req->headers = $new_header;
            }
        } else {
            $this->setError("Error setHeader: ", $header);
        }

        if (isset($this->req->headers) && (is_array($this->req->headers) && count($this->req->headers) > 0)) {
            $this->setOpt(\CURLOPT_HTTPHEADER, $this->req->headers);
        } else {
            unset($this->req->headers);
        }

        return $this;
    }

    /**
     * Request body set function
     * 
     * @param mixed|null $body 
     * @param string $type 
     * @return $this 
     * @throws C4NException 
     */
    public function setBody($body = null, $type = self::RAW)
    {
        if (isset($this->req->body) && (!empty($this->req->body))) {
            $this->setError("The request body already set", __FUNCTION__);
        } else {
            if (self::$method_properties[$this->req->method]['req_body']) {
                $this->req->body = $type($body);
                $this->req->body_type = $this->getConstName($type);
                $this->setOpt(\CURLOPT_POSTFIELDS, $this->req->body);
            } else {
                $this->setError("The request body cannot be used with this request method", $this->req->method);
            }
        }



        return $this;
    }

    /**
     * Send request function
     * 
     * @param string $method 
     * @param string $url 
     * @param array $headers 
     * @param string|null $body 
     * @return $this 
     * @throws C4NException 
     */
    public function send(string $method, string $url, array $headers = [], string $body = null, $body_type = self::RAW)
    {
        $this->setMethod($method);
        $this->setUrl($url);
        $this->setHeader($headers);
        if (self::$method_properties[$this->req->method]['req_body']) {
            $this->setBody($body, $body_type);
        }

        return $this;
    }

    /**
     * none
     * 
     * @param mixed $url 
     * @param array $headers 
     * @return $this 
     * @throws C4NException 
     */
    public function get($url, $headers = [])
    {
        $this->send(__FUNCTION__, $url, $headers);
        $this->setOpt(\CURLOPT_HTTPGET, true);

        return $this;
    }

    /**
     * "Post" method request function
     * 
     * @param mixed $url
     * @param array $headers
     * @param string|null $body
     * @return $this 
     */
    public function post($url, $headers = [], string $body = null, $body_type = self::RAW)
    {
        $this->send(__FUNCTION__, $url, $headers, $body, $body_type);
        $this->setOpt(\CURLOPT_POST, true);

        return $this;
    }

    /**
     * "Put" method request function
     * 
     * @param mixed $url
     * @param array $headers
     * @param mixed|null $body
     * @return $this 
     */
    public function put($url, $headers = [], string $body = null, $body_type = self::RAW)
    {
        $this->send(__FUNCTION__, $url, $headers, $body, $body_type);

        return $this;
    }

    /**
     * "Delete" method request function
     * 
     * @param mixed $url
     * @param array $headers
     * @param mixed|null $body
     * @return $this 
     */
    public function delete($url, $headers = [], string $body = null, $body_type = self::RAW)
    {
        $this->send(__FUNCTION__, $url, $headers, $body, $body_type);

        return $this;
    }

    /**
     * "Patch" method request function
     * 
     * @param mixed $url
     * @param array $headers
     * @param string|null $body
     * @return $this 
     */
    public function patch($url, $headers = [], string $body = null, $body_type = self::RAW)
    {
        $this->send(__FUNCTION__, $url, $headers, $body, $body_type);

        return $this;
    }

    /**
     * "Head" method request function
     * 
     * @param mixed $url
     * @param array $headers
     * @return $this 
     */
    public function head($url, $headers = [])
    {
        $this->send(__FUNCTION__, $url, $headers);
        $this->setOpt(\CURLOPT_NOBODY, true);

        return $this;
    }

    /**
     * "Connect" method request function
     * 
     * @param mixed $url
     * @param array $headers
     * @return $this 
     */
    public function connect($url, $headers = [])
    {
        $this->send(__FUNCTION__, $url, $headers);

        return $this;
    }

    /**
     * "Options" method request function
     * 
     * @param mixed $url
     * @param array $headers
     * @return $this 
     */
    public function options($url, $headers = [])
    {
        $this->send(__FUNCTION__, $url, $headers);

        return $this;
    }

    /**
     * "Trace" method request function
     * 
     * @param mixed $url
     * @param array $headers
     * @return $this 
     */
    public function trace($url, $headers = [])
    {
        $this->send(__FUNCTION__, $url, $headers);
        $this->setOpt(\CURLOPT_NOBODY, true);

        return $this;
    }

    /**
     * Curl exec and some parses
     * 
     * @return mixed
     */
    public function exec(): void
    {
        $response = curl_exec($this->req->ch);
        $header_size = $this->getInfo(\CURLINFO_HEADER_SIZE);
        $http_code = $this->getInfo(\CURLINFO_HTTP_CODE);
        $effective_url = $this->getInfo(\CURLINFO_EFFECTIVE_URL);
        $total_time = $this->getInfo(\CURLINFO_TOTAL_TIME);
        $headers = trim(substr($response, 0, intval($header_size)));
        if (self::$method_properties[$this->req->method]['res_body']) {
            $body = substr($response, intval($header_size));
        }
        $this->res->code = $http_code;
        $this->res->effective_url = $effective_url;
        $this->res->total_time = $total_time;
        $this->res->headers = $headers;
        $this->res->headers_array = $this->parseHeaders($headers);
        if (self::$method_properties[$this->req->method]['res_body']) {
            $this->res->body = $body;
        }
        if (curl_errno($this->req->ch)) {
            $this->res->error = curl_error($this->req->ch);
        }
        curl_close($this->req->ch);
    }

    /**
     * Curl setopt function short version
     * 
     * @param mixed $opt 
     * @param mixed $val 
     * @return $this  
     */
    public function setOpt($opt, $val)
    {
        curl_setopt($this->req->ch, $opt, $val);

        return $this;
    }

    /**
     * Curl getinfo function short version
     * 
     * @param mixed|null $opt 
     * @return $this  
     */
    public function getInfo($opt = null)
    {
        if (\is_null($opt)) {
            return curl_getinfo($this->req->ch);
        }
        return curl_getinfo($this->req->ch, $opt);
    }

    /**
     * Curl verbose
     * 
     * @param bool $bool 
     * @return $this  
     */
    public function setDebug(bool $bool = false)
    {
        $this->setOpt(\CURLOPT_VERBOSE, $bool);

        return $this;
    }

    /**
     * Curl cookie set
     * 
     * @param string $file
     * @return $this 
     */
    public function setCookieFile(string $file)
    {
        $this->setOpt(\CURLOPT_COOKIEFILE, $file);

        return $this;
    }

    /**
     * Curl cookie save
     * 
     * @param string $file
     * @return $this 
     */
    public function setCookieJar(string $file)
    {
        $this->setOpt(\CURLOPT_COOKIEJAR, $file);

        return $this;
    }

    /**
     * Curl follow location
     * 
     * @param bool $bool
     * @return $this 
     */
    public function setFollow(bool $bool = true)
    {
        $this->setOpt(\CURLOPT_FOLLOWLOCATION, $bool);

        return $this;
    }

    /**
     * Curl return transfer
     * 
     * @param bool $bool
     * @return $this 
     */
    public function setReturn(bool $bool = true)
    {
        $this->setOpt(\CURLOPT_RETURNTRANSFER, $bool);

        return $this;
    }

    /**
     * Curl auto referer
     * 
     * @param bool $bool
     * @return $this 
     */
    public function setAutoReferer(bool $bool = true)
    {
        $this->setOpt(\CURLOPT_AUTOREFERER, $bool);

        return $this;
    }

    /**
     * Curl auto referer
     * 
     * @param bool $bool
     * @return $this 
     */
    public function setAutoReferrer(bool $bool = true)
    {
        $this->setOpt(\CURLOPT_AUTOREFERER, $bool);

        return $this;
    }

    /**
     * Curl timeout
     * 
     * @param int $int
     * @return $this 
     */
    public function setTimeout(int $int = 5)
    {
        $this->setOpt(\CURLOPT_TIMEOUT, $int);

        return $this;
    }

    /**
     * Curl connect timeout
     * 
     * @param int $int
     * @return $this 
     */
    public function setConnectTimeout(int $int = 5)
    {
        $this->setOpt(\CURLOPT_CONNECTTIMEOUT, $int);

        return $this;
    }

    /**
     * Curl max connect
     * 
     * @param int $int
     * @return $this 
     */
    public function setMaxConnect(int $int = 5)
    {
        $this->setOpt(\CURLOPT_MAXCONNECTS, $int);

        return $this;
    }

    /**
     * Curl max redirect
     * 
     * @param int $int
     * @return $this 
     */
    public function setMaxRedirect(int $int = 5)
    {
        $this->setOpt(\CURLOPT_MAXREDIRS, $int);

        return $this;
    }

    /**
     * Curl proxy set function
     * 
     * @param string $proxy
     * @param mixed $port
     * @return $this 
     */
    public function setProxy(string $proxy, $port = null, $autoParse = false)
    {

        if ($autoParse && \is_null($port)) {
            $proxy_array = $this->proxyParse($proxy);

            if (count($proxy_array)) {
                extract($proxy_array);
            } else {
                $this->setError('Proxy auto parser did not work properly', 'null array');
            }

            if (isset($host) && !empty($host)) {
                $this->setOpt(\CURLOPT_PROXY, $host);
            } else {
                $this->setError('Proxy auto parser did not work properly', 'host parameter');
            }

            if (isset($port) && !empty($port)) {
                $this->setOpt(\CURLOPT_PROXYPORT, $port);
            }

            if (isset($method) && !empty($method)) {
                switch ($method) {
                    case 'http':
                        $this->setProxyType(\CURLPROXY_HTTP);
                        break;
                    case 'https':
                        $this->setProxyType(\CURLPROXY_HTTPS);
                        break;
                    case 'socks4':
                        $this->setProxyType(\CURLPROXY_SOCKS4A);
                        break;
                    case 'socks5':
                        $this->setProxyType(\CURLPROXY_SOCKS5_HOSTNAME);
                        break;
                    default:
                        $this->setProxyType(\CURLPROXY_HTTPS);
                        break;
                }
            }

            if ((isset($username) && !empty($username)) && (isset($password) && !empty($password))) {
                $this->setProxyAuth($username, $password);
            }
        } else {
            $proxyport = \is_null($port) ? $proxy : $proxy . ':' . $port;
            $this->setOpt(\CURLOPT_PROXY, $proxyport);
        }

        return $this;
    }

    /**
     * Curl proxy type set function
     * 
     * @param mixed $type
     * @return $this 
     */
    public function setProxyType($type)
    {
        $this->setOpt(\CURLOPT_PROXYTYPE, $type);

        return $this;
    }

    /**
     * Curl proxy auth set function
     * 
     * @param string $username
     * @param string $password
     * @return $this 
     */
    public function setProxyAuth(string $username, string $password = null)
    {
        $auth = \is_null($password) ? $username : $username . ':' . $password;
        $this->setOpt(\CURLOPT_PROXYUSERPWD, $auth);

        return $this;
    }

    /**
     * none
     * 
     * @param string $dns1 
     * @param string|null $dns2 
     * @return $this 
     */
    public function setDns(string $dns1, string $dns2 = null)
    {
        $dns = \is_null($dns2) ? $dns1 : $dns1 . ':' . $dns2;
        $this->setOpt(\CURLOPT_DNS_SERVERS, $dns);

        return $this;
    }

    /**
     * Getting request response
     * 
     * @return mixed 
     * @throws C4NException 
     */
    public function getResponse(bool $remove_line_break = false)
    {
        if (self::$method_properties[$this->req->method]['res_body']) {
            if ($remove_line_break) {
                return $this->htmlCompress($this->res->body);
            }
            return !isset($this->res->body) ? null : $this->res->body;
        } else {
            $this->setError("This request method does not return the response body", $this->req->method);
        }
    }

    /**
     * Getting request response as json
     * 
     * @param bool $array
     * @param int $flags
     * @return mixed
     */
    public function getRespJson($array = false, $flags = 0)
    {
        return json_decode($this->getResponse(), $array, 512, $flags);
    }

    public function find($search_datas, $source = null)
    {
        if (\is_null($source)) {
            $source = $this->getResponse();
        }

        if (\is_array($search_datas)) {
            foreach ($search_datas as $search_data) {
                $search_data = \preg_quote($search_data, '/');
                if (\preg_match('/' . $search_data . '/si', $source)) $result = json_encode(['result' => true, 'finded' => $search_data]);
            }
        } else {
            $search_data = \preg_quote($search_datas, '/');
            if (\preg_match('/' . $search_data . '/si', $source)) $result = json_encode(['result' => true, 'finded' => $search_datas]);
        }
        $null = json_encode(['result' => false]);
        return isset($result) ? json_decode($result) : json_decode($null);
    }

    /**
     * Getting string from request response
     * 
     * @param string $start
     * @param string $end
     * @return string
     */
    public function getBetween(string $start = '', string $end = '', bool $remove_line_break = false): string
    {
        $str = $this->getResponse($remove_line_break);
        $string = ' ' . $str;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * Getting strings from request response
     * 
     * @param string $start 
     * @param string $end 
     * @return array 
     */
    public function getBetweens(string $start = '', string $end = '', bool $remove_line_break = false): array
    {
        $n = explode($start, $this->getResponse($remove_line_break));
        $result = [];
        foreach ($n as $val) {
            $pos = strpos($val, $end);
            if ($pos !== false) {
                $result[] = substr($val, 0, $pos);
            }
        }
        return $result ?? '';
    }

    /**
     * Getting effective url from request response
     * 
     * @return mixed 
     */
    public function getEffective()
    {
        return $this->res->effective_url;
    }

    /**
     * Getting http status code from request response
     * 
     * @return int 
     */
    public function getHttpCode(): int
    {
        return intval($this->res->code);
    }

    /**
     * Getting header from request response
     * 
     * @param string $header 
     * @param int|null $header_id 
     * @return mixed 
     */
    public function getHeader(string $header, int $header_id = null)
    {
        if (\is_null($header_id)) {
            $header = end($this->res->headers_array)[$header] ?? false;
        } else {
            $header = $this->res->headers_array[$header_id][$header] ?? false;
        }
        return $header;
    }

    /**
     * Getting cookie from request response
     * 
     * @param string $cookie
     * @param int|null $header_id
     * @return mixed
     */
    public function getCookie(string $cookie, int $header_id = null)
    {
        if (\is_null($header_id)) {
            $cookie = end($this->res->headers_array)['set_cookie'][$cookie] ?? false;
        } else {
            $cookie = $this->res->headers_array[$header_id]['set_cookie'][$cookie] ?? false;
        }
        return $cookie;
    }

    /**
     * Getting raw cookies from request response
     * 
     * @param int|null $header_id
     * @return mixed
     */
    public function getCookiesRaw(int $header_id = null)
    {
        if (\is_null($header_id)) {
            $array = end($this->res->headers_array)['set_cookie'] ?? false;
            $cookie = '';
            foreach ($array as $key => $val) {
                $cookie .= $key . '=' . $val . '; ';
            }
        } else {
            $array = $this->res->headers_array[$header_id]['set_cookie'] ?? false;
            $cookie = '';
            foreach ($array as $key => $val) {
                $cookie .= $key . '=' . $val . '; ';
            }
        }
        $cookie = trim($cookie);
        return $cookie;
    }

    /**
     * Getting array cookies from request response
     * 
     * @param int|null $header_id
     * @return mixed
     */
    public function getCookiesArray(int $header_id = null)
    {
        if (\is_null($header_id)) {
            $cookie = end($this->res->headers_array)['set_cookie'] ?? false;
        } else {
            $cookie = $this->res->headers_array[$header_id]['set_cookie'] ?? false;
        }
        return $cookie;
    }

    /**
     * Parse request response headers
     * 
     * @param mixed $headers
     * @return array
     */
    private function parseHeaders(string $headers): array
    {
        $head = [];
        $headers = trim($headers);
        $new_headers = \preg_replace("@HTTP/@", "HTTP_EXPLODE\nHTTP/", $headers);
        $new_headers = array_filter(explode('HTTP_EXPLODE', $new_headers));
        $new_headers = array_map('trim', $new_headers);
        $new_headers = array_values($new_headers);
        foreach ($new_headers as $k1 => $v1) {
            $line_headers = explode(PHP_EOL, $v1);
            foreach ($line_headers as $k2 => $v2) {
                $t = explode(':', $v2, 2);
                if (isset($t[1])) {
                    if (strtolower(trim($t[0])) == 'set-cookie') {
                        \preg_match('@^([^=]+)=([^;]+);(.+)$@', trim($t[1]), $parts);
                        $head[$k1]["set_cookie"][$parts[1]] = $parts[2];
                    } else {
                        $head[$k1][trim($t[0])] = trim($t[1]);
                    }
                } else {
                    if (\preg_match("#HTTP/[\d\.]+\s+([\d]+)#", $v2, $out)) {
                        $head[$k1]['response_code'] = intval($out[1]);
                    }
                }
            }
        }
        return $head;
    }

    public function proxyParse($string)
    {
        \preg_match(self::PROXY_REGEX, $string, $matches);
        return $matches;
    }

    /**
     * HTML Compress
     * 
     * @param string $buffer 
     * @return string 
     */
    private function htmlCompress(string $buffer): string
    {
        $regex  = ['/\>[^\S ]+/s' => '>', '/[^\S ]+\</s' => '<', '/(\s)+/s' => '\\1'];
        $buffer = \preg_replace(array_keys($regex), array_values($regex), $buffer);
        $re = '%(?>[^\S ]\s*| \s{2,})(?=(?:(?:[^<]++| <(?!/?(?:textarea|pre)\b))*+)(?:<(?>textarea|pre)\b| \z))%ix';
        $buffer = \preg_replace($re, " ", $buffer);
        return $buffer;
    }
}