<?php
namespace C4N;

use ReflectionClass;

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
    public $req = null;
    public $res = null;

    /**
     * Proxy parser regex
     */
    private const PROXY_REGEX = '/^(?:(?<method>[http|https|socks4|socks5]*?):\/\/)?(?:(?<username>[\w0-9-_]*)(?::(?<password>[\w0-9-_]*))@)?(?<host>(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}|((?:\d{1,3})(?:\.\d{1,3}){3}))(?::(?<port>\d{1,5}))$/ms';

    /**
     * Curl request method properties
     */
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

    /**
     * @return void 
     * @throws Exception 
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) throw new \Exception("cURL extension is not loaded");
        $this->setDefault();
    }

    /**
     * Get curl error
     * 
     * @return mixed 
     */
    public function getCurlError()
    {
        return isset($this->res->error) && !\is_resource($this->req->ch) ?: $this->res->error;
    }

    /**
     * Request default options set function
     * 
     * @return $this
     */
    public function setDefault()
    {
        $this->req = (object)[];
        $this->req->ch = curl_init();
        $this->res = (object)[];

        $this->setOpt(\CURLOPT_HEADER, true);
        $this->setOpt(\CURLOPT_RETURNTRANSFER, true);

        return $this;
    }

    /**
     * Request method set function
     * 
     * @param string $method 
     * @return $this
     * @throws Exception 
     */
    public function setMethod(string $method)
    {
        if (isset($this->req->method) && !empty($this->req->method)) {
            throw new \Exception("Request method is already set");
        } else {
            $methodUP = strtoupper($method);
            if (isset(self::$method_properties[$methodUP])) {
                $this->req->method = $methodUP;
                $this->setOpt(\CURLOPT_CUSTOMREQUEST, $this->req->method);
            } else {
                throw new \Exception("Method {$method} is not supported");
            }
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
     * @throws Exception 
     */
    public function setHeader($header = null, string $value = "header_no_value")
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
            $new_header = $value == 'header_no_value' ? [$header] : [$header . ': ' . $value];
            if (isset($this->req->headers)) {
                $this->req->headers = array_merge($this->req->headers, $new_header);
            } else {
                $this->req->headers = $new_header;
            }
        } else {
            throw new \Exception("Header {$header} is not valid");
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
     * @throws Exception 
     */
    public function setBody($body = null, $type = self::RAW)
    {
        if (isset($this->req->body) && !empty($this->req->body)) {
            throw new \Exception("Request body is already set");
        } else {
            if (self::$method_properties[$this->req->method]['req_body']) {
                if (is_array($body) && $type == self::RAW) {
                    $type = self::QUERY;
                    $this->req->body = $type($body);
                    $this->req->body_type = $this->getConstName($type);
                } else {
                    $this->req->body = $type($body);
                    $this->req->body_type = $this->getConstName($type);
                }
                $this->setOpt(\CURLOPT_POSTFIELDS, $this->req->body);
            } else {
                throw new \Exception("Method {$this->req->method} does not support request body");
            }
        }

        return $this;
    }
    /**
     * Curl send function
     * 
     * @param string $method 
     * @param string $url 
     * @param array $headers 
     * @param mixed $body 
     * @param string $body_type 
     * @return $this 
     */
    public function send(string $method, string $url, array $headers = [], $body = null, $body_type = self::RAW)
    {
        $this->setDefault();
        $this->setMethod($method);
        $this->setUrl($url);
        $this->setHeader($headers);
        if (self::$method_properties[$this->req->method]['req_body']) {
            $this->setBody($body, $body_type);
        }

        return $this;
    }

    /**
     * "GET" request function
     * 
     * @param mixed $url 
     * @param array $headers 
     * @return $this 
     */
    public function get($url, $headers = [])
    {
        $this->send(__FUNCTION__, $url, $headers);
        $this->setOpt(\CURLOPT_HTTPGET, true);

        return $this;
    }

    /**
     * "Post" request function
     * 
     * @param mixed $url
     * @param array $headers
     * @param string|null $body
     * @param string $body_type
     * @return $this 
     */
    public function post($url, $headers = [], $body = null, $body_type = self::RAW)
    {
        $this->send(__FUNCTION__, $url, $headers, $body, $body_type);
        $this->setOpt(\CURLOPT_POST, true);

        return $this;
    }

    /**
     * "Put" request function
     * 
     * @param mixed $url
     * @param array $headers
     * @param mixed|null $body
     * @param string $body_type
     * @return $this 
     */
    public function put($url, $headers = [], $body = null, $body_type = self::RAW)
    {
        $this->send(__FUNCTION__, $url, $headers, $body, $body_type);

        return $this;
    }

    /**
     * "Delete" request function
     * 
     * @param mixed $url
     * @param array $headers
     * @param mixed|null $body
     * @param string $body_type
     * @return $this 
     */
    public function delete($url, $headers = [], $body = null, $body_type = self::RAW)
    {
        $this->send(__FUNCTION__, $url, $headers, $body, $body_type);

        return $this;
    }

    /**
     * "Patch" request function
     * 
     * @param mixed $url
     * @param array $headers
     * @param string|null $body
     * @param string $body_type
     * @return $this 
     */
    public function patch($url, $headers = [], $body = null, $body_type = self::RAW)
    {
        $this->send(__FUNCTION__, $url, $headers, $body, $body_type);

        return $this;
    }

    /**
     * "Head" request function
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
     * "Connect" request function
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
     * "Options" request function
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
     * "Trace" request function
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
     * @return void 
     */
    public function exec(): void
    {
        $response = (string)curl_exec($this->req->ch);
        $header_size = $this->curlGetInfo(\CURLINFO_HEADER_SIZE);
        $http_code = $this->curlGetInfo(\CURLINFO_HTTP_CODE);
        $effective_url = $this->curlGetInfo(\CURLINFO_EFFECTIVE_URL);
        $total_time = $this->curlGetInfo(\CURLINFO_TOTAL_TIME);
        $headers = trim(substr($response, 0, intval($header_size)));
        $this->res->info = $this->curlGetInfo();
        $this->res->code = $http_code;
        $this->res->effective_url = $effective_url;
        $this->res->total_time = $total_time;
        $this->res->headers = $headers;
        $this->res->headers_array = $this->parseHeaders($headers);
        if (self::$method_properties[$this->req->method]['res_body']) {
            $body = substr($response, intval($header_size));
            $this->res->body = $body;
        }
        if (curl_errno($this->req->ch)) {
            $this->res->error = curl_error($this->req->ch);
        }
        curl_close($this->req->ch);
        $this->req->ch = null;
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
        $set = curl_setopt($this->req->ch, $opt, $val);
        if ($set) $this->req->opt[$opt] = $val;

        return $this;
    }

    /**
     * Curl debug function
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
     * Curl cookie set from string
     * 
     * @param mixed $cookie 
     * @param mixed $value 
     * @return $this 
     * @throws Exception 
     */
    public function setCookie($cookie, $value = 'cookie_no_value')
    {
        if (is_string($cookie) && $value == 'cookie_no_value') {
            $this->setOpt(\CURLOPT_COOKIE, $cookie);
        } else if (is_string($cookie) && $value !== 'cookie_no_value') {
            $this->setOpt(\CURLOPT_COOKIE, $cookie . '=' . $value);
        } elseif (is_array($cookie)) {
            $cookie = http_build_query($cookie, '', '; ');
            $this->setOpt(\CURLOPT_COOKIE, $cookie);
        } else {
            throw new \Exception('Cookies name and value must be string or array');
        }

        return $this;
    }

    /**
     * Curl cookie set from file
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
     * Curl cookie save on file
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
     * Curl referer
     * 
     * @param bool $bool
     * @return $this 
     */
    public function setReferer(string $ref = null)
    {
        $this->setOpt(\CURLOPT_REFERER, $ref);

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
     * Curl timeout set
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
     * Curl connect timeout set
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
     * Curl max connect set
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
     * Curl max redirect set
     * 
     * @param int $int
     * @return $this 
     */
    public function setMaxRedirect(int $int = 20)
    {
        $this->setOpt(\CURLOPT_MAXREDIRS, $int);

        return $this;
    }

    /**
     * Curl proxy set
     * 
     * @param string $proxy
     * @param mixed $port
     * @param bool $autoParse
     * @return $this 
     * @throws Exception 
     */
    public function setProxy(string $proxy, $port_autoParse = true)
    {

        if (is_bool($port_autoParse) && $port_autoParse) {
            $proxy_array = $this->proxyParse($proxy);

            if (count($proxy_array) > 0 && is_array($proxy_array)) {
                extract($proxy_array);
            } else {
                throw new \Exception("Proxy parse error");
            }

            if (isset($host) && !empty($host)) {
                $this->setOpt(\CURLOPT_PROXY, $host);
            } else {
                throw new \Exception("Proxy host error");
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
            $port = $port_autoParse;
            $proxyport = $proxy . ':' . $port;
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
     * Curl user agent set function
     * 
     * @param string|null $useragent 
     * @return $this 
     */
    public function setUserAgent(string $useragent = null)
    {
        $this->setOpt(\CURLOPT_USERAGENT, $useragent);

        return $this;
    }

    /**
     * Curl getOpt function
     * 
     * @param mixed $opt 
     * @return mixed 
     */
    public function getOpt($opt = null)
    {
        if (\is_null($opt)) {
            return $this->req->opt;
        }
        return $this->req->opt[$opt];
    }

    /**
     * Curl getinfo function public short version
     * 
     * @param mixed $opt 
     * @return mixed 
     */
    public function getInfo($key = null)
    {
        if (\is_null($key)) {
            return $this->res->info;
        }
        return $this->res->info[$key];
    }

    /**
     * Getting request response
     * 
     * @return mixed 
     * @throws Exception 
     */
    public function getResponse(bool $remove_line_break = false)
    {
        if (self::$method_properties[$this->req->method]['res_body']) {
            if ($remove_line_break) {
                return $this->minifyHTML($this->res->body);
            }
            return $this->res->body;
        } else {
            throw new \Exception("Method {$this->req->method} does not support response body");
        }
    }

    /**
     * Getting request response as json
     * 
     * @param bool $array
     * @param int $flags
     * @return mixed
     * @throws Exception
     */
    public function getRespJson(bool $array = false, $flags = 0)
    {
        $json = json_decode($this->getResponse(), $array, 512, $flags);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        } else {
            throw new \Exception("Response json parse error: ". json_last_error_msg());
        }
    }

    /**
     * Getting effective url from request response
     * 
     * @return string 
     */
    public function getEffective(): string
    {
        return strval($this->res->effective_url);
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
     * @throws Exception
     */
    public function getHeader(string $header, int $header_id = null)
    {
        $headers = (array)$this->getHeaders($header_id);
        if (\array_key_exists($header, $headers)) {
            return $headers[$header];
        } else {
            throw new \Exception("Header {$header} not found");
        }
    }

    /**
     * Getting headers from request response
     * 
     * @param int|null $header_id 
     * @return mixed 
     * @throws Exception
     */
    public function getHeaders(int $header_id = null)
    {
        if (\is_null($header_id)) {
            $header = end($this->res->headers_array);
        } else {
            if (!isset($this->res->headers_array[$header_id])) {
                throw new \Exception("Header id {$header_id} not found");
            }
            $header = $this->res->headers_array[$header_id];
        }
        return $header ?? [];
    }

    /**
     * Getting cookie from request response
     * 
     * @param string $cookie
     * @param int|null $header_id
     * @return mixed
     * @throws Exception
     */
    public function getCookie(string $cookie, int $header_id = null)
    {
        $cookies = $this->getCookiesArray($header_id);
        if (\array_key_exists($cookie, $cookies)) {
            return $cookies[$cookie];
        } else {
            throw new \Exception("Cookie {$cookie} not found");
        }
    }

    /**
     * Getting raw cookies from request response
     * 
     * @param int|null $header_id
     * @return mixed
     */
    public function getCookiesRaw(int $header_id = null): string
    {
        $cookies = $this->getCookiesArray($header_id);
        $cookies = array_map(function($v, $k){return "$k=$v;";}, $cookies, array_keys($cookies));
        return implode(' ', $cookies);
    }

    /**
     * Getting array cookies from request response
     * 
     * @param int|null $header_id
     * @return mixed
     * @throws Exception
     */
    public function getCookiesArray(int $header_id = null): array
    {
        if (\is_null($header_id)) {
            $last_array = end($this->res->headers_array);
            $cookie_check = array_key_exists('set_cookie', $last_array);
            if ($cookie_check) $cookies = $last_array['set_cookie'];
            else throw new \Exception("Cookies not found");
        } else {
            if (!isset($this->res->headers_array[$header_id])) {
                throw new \Exception("Header id {$header_id} not found");
            }

            $select_array = $this->res->headers_array[$header_id];
            $cookie_check = array_key_exists('set_cookie', $select_array);
            if ($cookie_check) $cookies = $select_array['set_cookie'];
            else throw new \Exception("Cookies not found");
        }
        return $cookies ?? [];
    }

    /**
     * Find string from request response
     * 
     * @param mixed $search_datas 
     * @param mixed $source 
     * @return mixed 
     * @throws Exception 
     */
    public function find($search_datas, $source = null, bool $remove_line_break = false): object
    {
        $json = (object)[];
        $json->result = false;
        if (\is_null($source)) {
            $source = $this->getResponse($remove_line_break);
        }

        if (\is_array($search_datas)) {
            foreach ($search_datas as $search_data) {
                $search_data_regex = \preg_quote($search_data, '/');
                if (\preg_match('/' . $search_data_regex . '/si', $source) || $this->contains($search_data, $source)) {
                    $json->result = true;
                    $json->finded[] = $search_data;
                }
            }
        } else {
            $search_data = \preg_quote($search_datas, '/');
            if (\preg_match('/' . $search_data . '/si', $source) || $this->contains($search_data, $source)) {
                $json->result = true;
                $json->finded = $search_datas;
            }
        }

        return $json;
    }
    
    /**
     * Find string from text
     * 
     * @param mixed $texttosearch 
     * @param mixed $source 
     * @return bool 
     */
    public function contains($search_data, $source = null): bool
    {
        if (\is_null($source)) {
            $source = $this->getResponse();
        }

        if (\function_exists('str_contains')) {
            return str_contains($source, $search_data);
        }
        
        return strpos($source, $search_data) !== false;
    }

    /**
     * Getting string from request response or text data
     * 
     * @param string $start
     * @param string $end
     * @param string $source
     * @param bool $remove_line_break
     * @return string
     */
    public function getBetween(string $start = '', string $end = '', string $source = null, bool $include_delimiters = false, bool $remove_line_break = false, int &$offset = 0): ?string
    {
        if ($source === '' || $start === '' || $end === '') return null;

        if (\is_null($source)) $source = $this->getResponse($remove_line_break);

        $startLength = strlen($start);
        $endLength = strlen($end);

        $startPos = strpos($source, $start, $offset);
        if ($startPos === false) return null;

        $endPos = strpos($source, $end, $startPos + $startLength);
        if ($endPos === false) return null;

        $length = $endPos - $startPos + ($include_delimiters ? $endLength : -$startLength);
        if (!$length) return null;

        $offset = $startPos + ($include_delimiters ? 0 : $startLength);

        $result = substr($source, $offset, $length);

        return ($result !== false ? $result : null);
    }

    /**
     * Getting strings from request response or text data
     * 
     * @param string $start 
     * @param string $end 
     * @param string $source
     * @param bool $remove_line_break
     * @return array 
     */
    public function getBetweens(string $start = '', string $end = '', string $source = null, bool $include_delimiters = false, bool $remove_line_break = false, int &$offset = 0): ?array
    {
        if (\is_null($source)) $source = $this->getResponse($remove_line_break);

        $strings = [];
        $length = strlen($source);

        while ($offset < $length) {
            $found = $this->getBetween($start, $end, $source, $include_delimiters, $remove_line_break, $offset);
            if ($found === null) break;

            $strings[] = $found;
            $offset += strlen($include_delimiters ? $found : $start . $found . $end); // move offset to the end of the newfound string
        }

        return $strings;
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
                        \preg_match('@^(?<cookie_name>[^=]+)=(?<cookie_value>[^;]+)?;(.+)$@', trim($t[1]), $cookie_parts);
                        extract($cookie_parts);
                        $head[$k1]["set_cookie"][$cookie_name] = $cookie_value;
                    } else {
                        $head[$k1][trim($t[0])] = trim($t[1]);
                    }
                } else {
                    if (\preg_match("@HTTP/[\d\.]+\s+([\d]+)@", $v2, $out)) {
                        $head[$k1]['response_code'] = intval($out[1]);
                    }
                }
            }
        }
        return $head;
    }

    private function proxyParse($string): array
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
    private function minifyHTML(string $buffer): string
    {
        $regex  = ['/\>[^\S ]+/s' => '>', '/[^\S ]+\</s' => '<', '/(\s)+/s' => '\\1'];
        $buffer = \preg_replace(array_keys($regex), array_values($regex), $buffer);
        $re = '%(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre)\b))*+)(?:<(?>textarea|pre)\b|\z))%ix';
        $buffer = \preg_replace($re, " ", $buffer);
        return $buffer;
    }

    /**
     * Get const name in class
     * 
     * @param mixed $value 
     * @return int|string 
     */
    private static function getConstName($value)
    {
        $class = new ReflectionClass(__CLASS__);
        $constants = array_flip($class->getConstants());

        return $constants[$value];
    }

    /**
     * Curl getinfo function private short version
     * 
     * @param mixed $opt 
     * @return mixed 
     */
    private function curlGetInfo($opt = null)
    {
        if (\is_null($opt)) {
            return curl_getinfo($this->req->ch);
        }
        return curl_getinfo($this->req->ch, $opt);
    }
}
