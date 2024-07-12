<?php

/**
 * Curl class
 *
 * @package C4N
 * @author Mehmet Can
 */
#[\AllowDynamicProperties]
class Curl
{
	public const string QUERY = 'http_build_query';
	public const string JSON = 'json_encode';
	public const string RAW = 'strval';
	public array|object|null $request = null;
	public array|object|null $response = null;

	/**
	 * Proxy parser regex
	 */
	private const string PROXY_REGEX = '/^(?:(?<method>[http|https|socks4|socks5]*?):\/\/)?(?:(?<username>[\w0-9-_]*)(?::(?<password>[\w0-9-_]*))@)?(?<host>(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}|((?:\d{1,3})(?:\.\d{1,3}){3}))(?::(?<port>\d{1,5}))$/ms';

	/**
	 * Curl request method properties
	 */
	private static array $method_properties = [
		'GET' => [
			'request' => false,
			'response' => true,
			'options' => [CURLOPT_HTTPGET => true]
		],
		'POST' => [
			'request' => true,
			'response' => true,
			'options' => [CURLOPT_POST => true]
		],
		'PUT' => [
			'request' => true,
			'response' => true,
		],
		'DELETE' => [
			'request' => true,
			'response' => true, //optional
		],
		'PATCH' => [
			'request' => true,
			'response' => true,
		],
		'HEAD' => [
			'request' => false,
			'response' => false,
			'options' => [CURLOPT_NOBODY => true]
		],
		'CONNECT' => [
			'request' => false,
			'response' => true,
		],
		'OPTIONS' => [
			'request' => false,
			'response' => true,
		],
		'TRACE' => [
			'request' => false,
			'response' => false,
			'options' => [CURLOPT_NOBODY => true]
		],
	];

	/**
	 * @return void
	 * @throws Exception
	 */
	public function __construct()
	{
		if (!extension_loaded('curl')) {
			throw new Exception("cURL extension is not loaded");
		}

		$this->setDefault();
		$this->createMethods();
	}

	private function createMethods(): void
	{
		foreach (self::$method_properties as $methodName => $method) {
			$name = strtolower($methodName);

			if (!$method['request']) {
				$this->{$name} = fn (string $url, array $headers = []): Curl
					=> $this->send($name, $url, $headers)->setOptions($method['options'] ?? []);
			}

			if ($method['request']) {
				$this->{$name} = fn (string $url, array $headers = [], mixed $body = null, string $body_type = self::RAW): Curl
					=> $this->send($name, $url, $headers, $body, $body_type)->setOptions($method['options'] ?? []);
			}
		}
	}

	public function __call($name, $arguments)
	{
		return call_user_func_array($this->{$name}, $arguments);
	}

	/**
	 * Get curl error
	 *
	 * @return mixed
	 */
	public function getCurlError(): mixed
	{
		return isset($this->response->error) && !is_resource($this->request->channel) ?: $this->response->error;
	}

	/**
	 * Request default options set function
	 *
	 * @return Curl
	 */
	public function setDefault(): self
	{
		$this->request = (object)[];
		$this->request->channel = curl_init();
		$this->response = (object)[];

		$this->setOpt(CURLOPT_HEADER, true)
			->setOpt(CURLOPT_RETURNTRANSFER, true);

		return $this;
	}

	/**
	 * Request method set function
	 *
	 * @param string $method
	 * @return Curl
	 * @throws Exception
	 */
	public function setMethod(string $method): self
	{
		if (isset($this->request->method) && !empty($this->request->method)) {
			throw new Exception("Request method is already set");
		}

		$upper = strtoupper($method);
		if (isset(self::$method_properties[$upper])) {
			$this->request->method = $upper;
			$this->setOpt(CURLOPT_CUSTOMREQUEST, $this->request->method);
		} else {
			throw new Exception("Method $method is not supported");
		}
		return $this;
	}

	/**
	 * Request url set function
	 *
	 * @param string $url
	 * @return Curl
	 */
	public function setUrl(string $url): self
	{
		$this->request->url = $url;
		$this->setOpt(CURLOPT_URL, $this->request->url);

		return $this;
	}

	/**
	 * Request header set function
	 *
	 * @param mixed|null $header
	 * @param string $value
	 * @return Curl
	 * @throws Exception
	 */
	public function setHeader(mixed $header = null, string $value = "header_no_value"): self
	{
		if (is_array($header)) {
			$new_header = null;
			foreach ($header as $key => $value) {
				if (is_string($key) && !is_array($value)) {
					$new_header[] = $key . ': ' . $value;
				} else {
					$new_header[] = $value;
				}
			}
			if (!is_null($new_header)) {
				$header = $new_header;
			}
			if (isset($this->request->headers)) {
				$this->request->headers = array_merge($this->request->headers, $header);
			} else {
				$this->request->headers = $header;
			}
		} elseif (is_string($header)) {
			$new_header = $value === 'header_no_value' ? [$header] : [$header . ': ' . $value];
			if (isset($this->request->headers)) {
				$this->request->headers = array_merge($this->request->headers, $new_header);
			} else {
				$this->request->headers = $new_header;
			}
		} else {
			throw new Exception("Header $header is not valid");
		}

		if (isset($this->request->headers) && (is_array($this->request->headers) && count($this->request->headers) > 0)) {
			$this->setOpt(CURLOPT_HTTPHEADER, $this->request->headers);
		} else {
			unset($this->request->headers);
		}

		return $this;
	}

	/**
	 * Request body set function
	 *
	 * @param mixed|null $body
	 * @param string $type
	 * @return Curl
	 * @throws Exception
	 */
	public function setBody(mixed $body = null, string $type = self::RAW): self
	{
		if (isset($this->request->body) && !empty($this->request->body)) {
			throw new Exception("Request body is already set");
		}

		if (self::$method_properties[$this->request->method]['request']) {
			if (is_array($body) && $type === self::RAW) {
				$type = self::QUERY;
			}
			$this->request->body = $type($body);
			$this->request->body_type = self::getConstName($type);
			$this->setOpt(CURLOPT_POSTFIELDS, $this->request->body);
		} else {
			throw new Exception("Method $this->request->method does not support request body");
		}

		return $this;
	}

	/**
	 * Curl send function
	 *
	 * @param string $method
	 * @param string $url
	 * @param array $headers
	 * @param mixed|null $body
	 * @param string $body_type
	 * @return Curl
	 * @throws Exception
	 */
	public function send(string $method, string $url, array $headers = [], mixed $body = null, string $body_type = self::RAW): self
	{
		$this->setDefault()
			->setMethod($method)
			->setUrl($url)
			->setHeader($headers);

		if (self::$method_properties[$this->request->method]['request']) {
			$this->setBody($body, $body_type);
		}

		return $this;
	}

	/**
	 * Curl exec and some parses
	 *
	 * @return void
	 */
	public function exec(): void
	{
		$response = (string)curl_exec($this->request->channel);
		$header_size = $this->curlGetInfo(CURLINFO_HEADER_SIZE);
		$http_code = $this->curlGetInfo(CURLINFO_HTTP_CODE);
		$effective_url = $this->curlGetInfo(CURLINFO_EFFECTIVE_URL);
		$total_time = $this->curlGetInfo(CURLINFO_TOTAL_TIME);
		$headers = trim(substr($response, 0, (int)$header_size));
		$this->response->info = $this->curlGetInfo();
		$this->response->code = $http_code;
		$this->response->effective_url = $effective_url;
		$this->response->total_time = $total_time;
		$this->response->headers = $headers;
		$this->response->headers_array = $this->parseHeaders($headers);
		if (self::$method_properties[$this->request->method]['response']) {
			$body = substr($response, (int)$header_size);
			$this->response->body = $body;
		}
		if (curl_errno($this->request->channel)) {
			$this->response->error = curl_error($this->request->channel);
		}
		curl_close($this->request->channel);
		$this->request->channel = null;
	}

	/**
	 * Curl setopt function short version
	 *
	 * @param mixed $opt
	 * @param mixed $val
	 * @return Curl
	 */
	public function setOpt(mixed $opt, mixed $val): self
	{
		$set = curl_setopt($this->request->channel, $opt, $val);
		if ($set) {
			$this->request->opt[$opt] = $val;
		}

		return $this;
	}

	/**
	 * Curl setopt function short version
	 *
	 * @param array $options
	 * @return Curl
	 */
	public function setOptions(array $options): self
	{
		foreach ($options as $option => $optionValue) {
			$this->setOpt($option, $optionValue);
		}

		return $this;
	}

	/**
	 * Curl debug function
	 *
	 * @param bool $bool
	 * @return Curl
	 */
	public function setDebug(bool $bool = false): self
	{
		$this->setOpt(CURLOPT_VERBOSE, $bool);

		return $this;
	}

	/**
	 * Curl cookie set from string
	 *
	 * @param mixed $cookie
	 * @param mixed $value
	 * @return Curl
	 * @throws Exception
	 */
	public function setCookie(mixed $cookie, mixed $value = 'cookie_no_value'): self
	{
		if (is_string($cookie) && $value === 'cookie_no_value') {
			$this->setOpt(CURLOPT_COOKIE, $cookie);
		} else if (is_string($cookie) && $value !== 'cookie_no_value') {
			$this->setOpt(CURLOPT_COOKIE, $cookie . '=' . $value);
		} elseif (is_array($cookie)) {
			$cookie = http_build_query($cookie, '', '; ');
			$this->setOpt(CURLOPT_COOKIE, $cookie);
		} else {
			throw new Exception('Cookies name and value must be string or array');
		}

		return $this;
	}

	/**
	 * Curl cookie set from file
	 *
	 * @param string $file
	 * @return Curl
	 */
	public function setCookieFile(string $file): self
	{
		$this->setOpt(CURLOPT_COOKIEFILE, $file);

		return $this;
	}

	/**
	 * Curl cookie save on file
	 *
	 * @param string $file
	 * @return Curl
	 */
	public function setCookieJar(string $file): self
	{
		$this->setOpt(CURLOPT_COOKIEJAR, $file);

		return $this;
	}

	/**
	 * Curl follow location
	 *
	 * @param bool $bool
	 * @return Curl
	 */
	public function setFollow(bool $bool = true): self
	{
		$this->setOpt(CURLOPT_FOLLOWLOCATION, $bool);

		return $this;
	}

	/**
	 * Curl return transfer
	 *
	 * @param bool $bool
	 * @return Curl
	 */
	public function setReturn(bool $bool = true): self
	{
		$this->setOpt(CURLOPT_RETURNTRANSFER, $bool);

		return $this;
	}

	/**
	 * Curl referer
	 *
	 * @param string|null $referer
	 * @return Curl
	 */
	public function setReferer(string $referer = null): self
	{
		$this->setOpt(CURLOPT_REFERER, $referer);

		return $this;
	}

	/**
	 * Curl auto referer
	 *
	 * @param bool $bool
	 * @return Curl
	 */
	public function setAutoReferer(bool $bool = true): self
	{
		$this->setOpt(CURLOPT_AUTOREFERER, $bool);

		return $this;
	}

	/**
	 * Curl timeout set
	 *
	 * @param int $int
	 * @return Curl
	 */
	public function setTimeout(int $int = 5): self
	{
		$this->setOpt(CURLOPT_TIMEOUT, $int);

		return $this;
	}

	/**
	 * Curl connect timeout set
	 *
	 * @param int $int
	 * @return Curl
	 */
	public function setConnectTimeout(int $int = 5): self
	{
		$this->setOpt(CURLOPT_CONNECTTIMEOUT, $int);

		return $this;
	}

	/**
	 * Curl max connect set
	 *
	 * @param int $int
	 * @return Curl
	 */
	public function setMaxConnect(int $int = 5): self
	{
		$this->setOpt(CURLOPT_MAXCONNECTS, $int);

		return $this;
	}

	/**
	 * Curl max redirect set
	 *
	 * @param int $int
	 * @return Curl
	 */
	public function setMaxRedirect(int $int = 20): self
	{
		$this->setOpt(CURLOPT_MAXREDIRS, $int);

		return $this;
	}

	/**
	 * Curl proxy set
	 *
	 * @param string $proxy
	 * @param bool|string $autoParse
	 * @return Curl
	 * @throws Exception
	 */
	public function setProxy(string $proxy, bool|string $autoParse = true): self
	{

		if (is_bool($autoParse) && $autoParse) {
			$proxy_array = $this->proxyParse($proxy);

			if (count($proxy_array) > 0 && is_array($proxy_array)) {
				extract($proxy_array);
			} else {
				throw new Exception("Proxy parse error");
			}

			if (isset($host) && !empty($host)) {
				$this->setOpt(CURLOPT_PROXY, $host);
			} else {
				throw new Exception("Proxy host error");
			}

			if (isset($port) && !empty($port)) {
				$this->setOpt(CURLOPT_PROXYPORT, $port);
			}

			if (isset($method) && !empty($method)) {
				switch ($method) {
					case 'http':
						$this->setProxyType(CURLPROXY_HTTP);
						break;
					case 'socks4':
						$this->setProxyType(CURLPROXY_SOCKS4A);
						break;
					case 'socks5':
						$this->setProxyType(CURLPROXY_SOCKS5_HOSTNAME);
						break;
					default:
						$this->setProxyType(CURLPROXY_HTTPS);
						break;
				}
			}

			if ((isset($username) && !empty($username)) && (isset($password) && !empty($password))) {
				$this->setProxyAuth($username, $password);
			}
		} else {
			$port = $autoParse;
			$proxyport = $proxy . ':' . $port;
			$this->setOpt(CURLOPT_PROXY, $proxyport);
		}

		return $this;
	}

	/**
	 * Curl proxy type set function
	 *
	 * @param mixed $type
	 * @return Curl
	 */
	public function setProxyType(mixed $type): self
	{
		$this->setOpt(CURLOPT_PROXYTYPE, $type);

		return $this;
	}

	/**
	 * Curl proxy auth set function
	 *
	 * @param string $username
	 * @param string|null $password
	 * @return Curl
	 */
	public function setProxyAuth(string $username, string $password = null): self
	{
		$auth = is_null($password) ? $username : $username . ':' . $password;
		$this->setOpt(CURLOPT_PROXYUSERPWD, $auth);

		return $this;
	}

	/**
	 * Curl user agent set function
	 *
	 * @param string|null $useragent
	 * @return Curl
	 */
	public function setUserAgent(string $useragent = null): self
	{
		$this->setOpt(CURLOPT_USERAGENT, $useragent);

		return $this;
	}

	/**
	 * Curl getOpt function
	 *
	 * @param mixed|null $opt
	 * @return mixed
	 */
	public function getOpt(mixed $opt = null): mixed
	{
		if (is_null($opt)) {
			return $this->request->opt;
		}

		return $this->request->opt[$opt];
	}

	/**
	 * Curl getinfo function public short version
	 *
	 * @param string|int|null $key
	 * @return mixed
	 */
	public function getInfo(string|int $key = null): mixed
	{
		if (is_null($key)) {
			return $this->response->info;
		}

		return $this->response->info[$key];
	}

	/**
	 * Getting request response
	 *
	 * @param bool $remove_line_break
	 * @return string
	 */
	public function getResponse(bool $remove_line_break = false): string
	{
		if (self::$method_properties[$this->request->method]['response']) {
			if ($remove_line_break) {
				return $this->minifyHTML($this->response->body);
			}
			return $this->response->body;
		}

		throw new RuntimeException("Method {$this->request->method} does not support response body");
	}

	/**
	 * Getting request response as json
	 *
	 * @param bool $array
	 * @param int $flags
	 * @return mixed
	 * @throws Exception
	 */
	public function getRespJson(bool $array = false, int $flags = 0): mixed
	{
		$json = json_decode($this->getResponse(), $array, 512, JSON_THROW_ON_ERROR | $flags);

		if (json_last_error() === JSON_ERROR_NONE) {
			return $json;
		}

		throw new Exception("Response json parse error: " . json_last_error_msg());
	}

	/**
	 * Getting effective url from request response
	 *
	 * @return string
	 */
	public function getEffective(): string
	{
		return (string)$this->response->effective_url;
	}

	/**
	 * Getting http status code from request response
	 *
	 * @return int
	 */
	public function getHttpCode(): int
	{
		return (int)$this->response->code;
	}

	/**
	 * Getting header from request response
	 *
	 * @param string $header
	 * @param int|null $header_id
	 * @return mixed
	 * @throws Exception
	 */
	public function getHeader(string $header, int $header_id = null): mixed
	{
		$headers = (array)$this->getHeaders($header_id);
		if (array_key_exists($header, $headers)) {
			return $headers[$header];
		}

		throw new Exception("Header $header not found");
	}

	/**
	 * Getting headers from request response
	 *
	 * @param int|null $header_id
	 * @return mixed
	 * @throws Exception
	 */
	public function getHeaders(int $header_id = null): mixed
	{
		if (is_null($header_id)) {
			$header = end($this->response->headers_array);
		} else {
			if (!isset($this->response->headers_array[$header_id])) {
				throw new Exception("Header id $header_id not found");
			}
			$header = $this->response->headers_array[$header_id];
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
	public function getCookie(string $cookie, int $header_id = null): mixed
	{
		$cookies = $this->getCookiesArray($header_id);
		if (array_key_exists($cookie, $cookies)) {
			return $cookies[$cookie];
		}

		throw new Exception("Cookie $cookie not found");
	}

	/**
	 * Getting raw cookies from request response
	 *
	 * @param int|null $header_id
	 * @return mixed
	 * @throws Exception
	 */
	public function getCookiesRaw(int $header_id = null): string
	{
		$cookies = $this->getCookiesArray($header_id);
		$cookies = array_map(function ($v, $k) {
			return "$k=$v;";
		}, $cookies, array_keys($cookies));
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
		if (is_null($header_id)) {
			$last_array = end($this->response->headers_array);
			$cookie_check = array_key_exists('set_cookie', $last_array);
			if ($cookie_check) {
				$cookies = $last_array['set_cookie'];
			} else {
				throw new Exception("Cookies not found");
			}
		} else {
			if (!isset($this->response->headers_array[$header_id])) {
				throw new Exception("Header id $header_id not found");
			}

			$select_array = $this->response->headers_array[$header_id];
			$cookie_check = array_key_exists('set_cookie', $select_array);
			if ($cookie_check) {
				$cookies = $select_array['set_cookie'];
			} else {
				throw new Exception("Cookies not found");
			}
		}
		return $cookies ?? [];
	}

	/**
	 * Find string from request response
	 *
	 * @param mixed $searches_datas
	 * @param mixed $source
	 * @param bool $remove_line_break
	 * @return mixed
	 * @throws Exception
	 */
	public function find(mixed $searches_datas, $source = null, bool $remove_line_break = false): object
	{
		$json = (object)[];
		$json->result = false;
		if (is_null($source)) {
			$source = $this->getResponse($remove_line_break);
		}

		if (is_array($searches_datas)) {
			foreach ($searches_datas as $search_data) {
				$search_data_regex = preg_quote($search_data, '/');
				if (preg_match('/' . $search_data_regex . '/si', $source) || $this->contains($search_data, $source)) {
					$json->result = true;
					$json->finded[] = $search_data;
				}
			}
		} else {
			$search_data = preg_quote($searches_datas, '/');
			if (preg_match('/' . $search_data . '/si', $source) || $this->contains($search_data, $source)) {
				$json->result = true;
				$json->finded = $searches_datas;
			}
		}

		return $json;
	}

	/**
	 * Find string from text
	 *
	 * @param $search_data
	 * @param mixed|null $source
	 * @return bool
	 * @throws Exception
	 */
	public function contains($search_data, mixed $source = null): bool
	{
		if (is_null($source)) {
			$source = $this->getResponse();
		}

		return str_contains($source, $search_data);
	}

	/**
	 * Getting string from request response or text data
	 *
	 * @param string $start
	 * @param string $end
	 * @param string|null $source
	 * @param bool $include_delimiters
	 * @param bool $remove_line_break
	 * @param int $offset
	 * @return string|null
	 * @throws Exception
	 */
	public function getBetween(string $start = '', string $end = '', string $source = null, bool $include_delimiters = false, bool $remove_line_break = false, int &$offset = 0): ?string
	{
		if ($source === '' || $start === '' || $end === '') {
			return null;
		}

		if (is_null($source)) {
			$source = $this->getResponse($remove_line_break);
		}

		$startLength = strlen($start);
		$endLength = strlen($end);

		$startPos = strpos($source, $start, $offset);
		if ($startPos === false) {
			return null;
		}

		$endPos = strpos($source, $end, $startPos + $startLength);
		if ($endPos === false) {
			return null;
		}

		$length = $endPos - $startPos + ($include_delimiters ? $endLength : -$startLength);
		if (!$length) {
			return null;
		}

		$offset = $startPos + ($include_delimiters ? 0 : $startLength);

		return (substr($source, $offset, $length));
	}

	/**
	 * Getting strings from request response or text data
	 *
	 * @param string $start
	 * @param string $end
	 * @param string|null $source
	 * @param bool $include_delimiters
	 * @param bool $remove_line_break
	 * @param int $offset
	 * @return array|null
	 * @throws Exception
	 */
	public function getBetweens(string $start = '', string $end = '', string $source = null, bool $include_delimiters = false, bool $remove_line_break = false, int &$offset = 0): ?array
	{
		if (is_null($source)) {
			$source = $this->getResponse($remove_line_break);
		}

		$strings = [];
		$length = strlen($source);

		while ($offset < $length) {
			$found = $this->getBetween($start, $end, $source, $include_delimiters, $remove_line_break, $offset);
			if ($found === null) {
				break;
			}

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
		$new_headers = preg_replace("@HTTP/@", "HTTP_EXPLODE\nHTTP/", $headers);
		$new_headers = array_filter(explode('HTTP_EXPLODE', $new_headers));
		$new_headers = array_map('trim', $new_headers);
		$new_headers = array_values($new_headers);
		foreach ($new_headers as $k1 => $v1) {
			$line_headers = explode(PHP_EOL, $v1);
			foreach ($line_headers as $v2) {
				$t = explode(':', $v2, 2);
				if (isset($t[1])) {
					if (strtolower(trim($t[0])) === 'set-cookie') {
						preg_match('@^(?<cookie_name>[^=]+)=(?<cookie_value>[^;]+)?;(.+)$@', trim($t[1]), $cookie_parts);
						extract($cookie_parts);
						$head[$k1]["set_cookie"][$cookie_name] = $cookie_value;
					} else {
						$head[$k1][trim($t[0])] = trim($t[1]);
					}
				} else if (preg_match("@HTTP/[\d\.]+\s+([\d]+)@", $v2, $out)) {
					$head[$k1]['response_code'] = (int)$out[1];
				}
			}
		}
		return $head;
	}

	private function proxyParse($string): array
	{
		preg_match(self::PROXY_REGEX, $string, $matches);
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
		$regex = ['/\>[^\S ]+/s' => '>', '/[^\S ]+\</s' => '<', '/(\s)+/s' => '\\1'];
		$buffer = preg_replace(array_keys($regex), array_values($regex), $buffer);
		$re = '%(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre)\b))*+)(?:<(?>textarea|pre)\b|\z))%ix';
		return preg_replace($re, " ", $buffer);
	}

	/**
	 * Get const name in class
	 *
	 * @param mixed $value
	 * @return int|string
	 */
	private static function getConstName(mixed $value): int|string
	{
		$class = new ReflectionClass(__CLASS__);
		$constants = array_flip($class->getConstants());

		return $constants[$value];
	}

	/**
	 * Curl getinfo function private short version
	 *
	 * @param mixed|null $opt
	 * @return mixed
	 */
	private function curlGetInfo(mixed $opt = null): mixed
	{
		if (is_null($opt)) {
			return curl_getinfo($this->request->channel);
		}
		return curl_getinfo($this->request->channel, $opt);
	}
}
