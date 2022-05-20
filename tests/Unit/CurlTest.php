<?php

namespace Tests\Unit\C4N;

use C4N\Curl;
use C4N\CurlException;
use PHPUnit\Framework\TestCase;

/**
 * Class CurlTest.
 *
 * @author Mehmet Can
 *
 * @covers \C4N\Curl
 */
class CurlTest extends TestCase
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->curl = new Curl();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->curl);
    }

    public function testSetDefault(): void
    {
        $this->curl->setDefault();
        $this->assertIsResource($this->curl->req->ch);
    }

    public function testSetMethod(): void
    {
        $this->curl->setMethod("GET");
        $this->assertEquals('GET', $this->curl->req->method);
    }

    public function testSetUrl(): void
    {
        $a = $this->curl->setUrl("https://httpbin.org/get");
        $this->assertEquals('https://httpbin.org/get', $a->req->url);
    }

    public function testSetHeader(): void
    {
        $a = $this->curl->setHeader("TestHeader-1: value 1");
        $this->assertContains('TestHeader-1: value 1', $a->req->headers);

        $a = $this->curl->setHeader("TestHeader-2", "value 2");
        $this->assertContains('TestHeader-2: value 2', $a->req->headers);

        $a = $this->curl->setHeader("TestHeader-3", "curl_no_value");
        $this->assertContains('TestHeader-3', $a->req->headers);
    }

    public function testSetBody(): void
    {
        $a = $this->curl->setMethod("POST");
        $body = $a->setBody("TestBody1");
        $this->assertEquals('TestBody1', $body->req->body);
        
        $a = $this->curl->setMethod("POST");
        $body = $a->setBody("TestBody2", $a::RAW);
        $this->assertEquals('TestBody2', $body->req->body);
        
        $a = $this->curl->setMethod("POST");
        $body = $a->setBody(["TestBody3"], $a::JSON);
        $this->assertEquals(\json_encode(["TestBody3"]), $body->req->body);

        $a = $this->curl->setMethod("POST");
        $body = $a->setBody(["search" => "TestBody4"], $a::QUERY);
        $this->assertEquals("search=TestBody4", $body->req->body);
    }

    public function testSend(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->send("GET", "https://httpbin.org/get", $headers);
        $this->assertEquals('GET', $this->curl->req->method);
        $this->assertEquals('https://httpbin.org/get', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);

        $this->curl->send("POST", "https://httpbin.org/post", $headers, ["TestBody1"], $this->curl::JSON);
        $this->assertEquals('POST', $this->curl->req->method);
        $this->assertEquals(\json_encode(["TestBody1"]), $this->curl->req->body);
        $this->assertEquals('https://httpbin.org/post', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testGet(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->get("https://httpbin.org/get", $headers);
        $this->assertEquals('GET', $this->curl->req->method);
        $this->assertEquals('https://httpbin.org/get', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testPost(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->post("https://httpbin.org/post", $headers, ["TestBody1"], $this->curl::JSON);
        $this->assertEquals('POST', $this->curl->req->method);
        $this->assertEquals(\json_encode(["TestBody1"]), $this->curl->req->body);
        $this->assertEquals('https://httpbin.org/post', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testPut(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->put("https://httpbin.org/put", $headers, ["TestBody1"], $this->curl::JSON);
        $this->assertEquals('PUT', $this->curl->req->method);
        $this->assertEquals(\json_encode(["TestBody1"]), $this->curl->req->body);
        $this->assertEquals('https://httpbin.org/put', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testDelete(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->delete("https://httpbin.org/delete", $headers);
        $this->assertEquals('DELETE', $this->curl->req->method);
        $this->assertEquals('https://httpbin.org/delete', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testPatch(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->patch("https://httpbin.org/patch", $headers, ["TestBody1"], $this->curl::JSON);
        $this->assertEquals('PATCH', $this->curl->req->method);
        $this->assertEquals(\json_encode(["TestBody1"]), $this->curl->req->body);
        $this->assertEquals('https://httpbin.org/patch', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testHead(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->head("https://httpbin.org/head", $headers);
        $this->assertEquals('HEAD', $this->curl->req->method);
        $this->assertEquals('https://httpbin.org/head', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testConnect(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->connect("https://httpbin.org/connect", $headers);
        $this->assertEquals('CONNECT', $this->curl->req->method);
        $this->assertEquals('https://httpbin.org/connect', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testOptions(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->options("https://httpbin.org/options", $headers);
        $this->assertEquals('OPTIONS', $this->curl->req->method);
        $this->assertEquals('https://httpbin.org/options', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testTrace(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->trace("https://httpbin.org/trace", $headers);
        $this->assertEquals('TRACE', $this->curl->req->method);
        $this->assertEquals('https://httpbin.org/trace', $this->curl->req->url);
        $this->assertContains('TestHeader-1: value 1', $this->curl->req->headers);
        $this->assertContains('TestHeader-2: value 2', $this->curl->req->headers);
    }

    public function testSetOpt(): void
    {
        $this->curl->setOpt(CURLOPT_URL, "https://httpbin.org/get");
        $this->assertEquals('https://httpbin.org/get', $this->curl->getOpt(\CURLOPT_URL));
    }

    public function testGetInfo(): void
    {
        $headers = [
            "TestHeader-1: value 1",
            "TestHeader-2: value 2"
        ];
        $this->curl->get("https://httpbin.org/get", $headers);
        $this->curl->exec();
        $this->assertEquals('GET', $this->curl->req->method);
        $this->assertEquals('https://httpbin.org/get', $this->curl->req->url);
    }

    public function testSetDebug(): void
    {
        $this->curl->setDebug(true);
        $this->assertEquals(true, $this->curl->getOpt(\CURLOPT_VERBOSE));
    }

    public function testSetCookieFile(): void
    {
        $this->curl->setCookieFile('./cookie.txt');
        $this->assertEquals('./cookie.txt', $this->curl->getOpt(\CURLOPT_COOKIEFILE));
    }

    public function testSetCookieJar(): void
    {
        $this->curl->setCookieJar('./cookie.txt');
        $this->assertEquals('./cookie.txt', $this->curl->getOpt(\CURLOPT_COOKIEJAR));
    }

    public function testSetFollow(): void
    {
        $this->curl->setFollow(true);
        $this->assertTrue($this->curl->getOpt(\CURLOPT_FOLLOWLOCATION));
        $this->curl->setFollow(false);
        $this->assertFalse($this->curl->getOpt(\CURLOPT_FOLLOWLOCATION));
    }

    public function testSetReturn(): void
    {
        $this->curl->setReturn(true);
        $this->assertTrue($this->curl->getOpt(\CURLOPT_RETURNTRANSFER));
        $this->curl->setReturn(false);
        $this->assertFalse($this->curl->getOpt(\CURLOPT_RETURNTRANSFER));
    }

    public function testSetReferer(): void
    {
        $this->curl->setReferer("https://httpbin.org/");
        $this->assertEquals('https://httpbin.org/', $this->curl->getOpt(\CURLOPT_REFERER));
    }

    public function testSetAutoReferer(): void
    {
        $this->curl->setAutoReferer(true);
        $this->assertTrue($this->curl->getOpt(\CURLOPT_AUTOREFERER));
        $this->curl->setAutoReferer(false);
        $this->assertFalse($this->curl->getOpt(\CURLOPT_AUTOREFERER));
    }

    public function testSetTimeout(): void
    {
        $this->curl->setTimeout(5);
        $this->assertIsInt($this->curl->getOpt(\CURLOPT_TIMEOUT));
    }

    public function testSetConnectTimeout(): void
    {
        $this->curl->setTimeout(5);
        $this->assertIsInt($this->curl->getOpt(\CURLOPT_TIMEOUT));
    }

    public function testSetMaxConnect(): void
    {
        $this->curl->setMaxConnect(5);
        $this->assertIsInt($this->curl->getOpt(\CURLOPT_MAXCONNECTS));
    }

    public function testSetMaxRedirect(): void
    {
        $this->curl->setMaxRedirect(5);
        $this->assertIsInt($this->curl->getOpt(\CURLOPT_MAXREDIRS));
    }

    public function testSetProxy(): void
    {
        $this->curl->setProxy('https://127.0.0.1:8080');
        $this->assertEquals('127.0.0.1', $this->curl->getOpt(\CURLOPT_PROXY));
        $this->assertEquals(8080, $this->curl->getOpt(\CURLOPT_PROXYPORT));
        $this->assertEquals(\CURLPROXY_HTTPS, $this->curl->getOpt(\CURLOPT_PROXYTYPE));
    }

    public function testSetProxyType(): void
    {
        $this->curl->setProxyType(CURLPROXY_HTTPS);
        $this->assertEquals(CURLPROXY_HTTPS, $this->curl->getOpt(\CURLOPT_PROXYTYPE));
    }

    public function testSetProxyAuth(): void
    {
        $this->curl->setProxyAuth('user:pass');
        $this->assertEquals('user:pass', $this->curl->getOpt(\CURLOPT_PROXYUSERPWD));

        $this->curl->setProxyAuth('user', 'pass');
        $this->assertEquals('user:pass', $this->curl->getOpt(\CURLOPT_PROXYUSERPWD));
    }

    public function testGetResponse(): void
    {
        $this->curl->get("https://httpbin.org/get");
        $this->curl->exec();
        $this->assertIsString($this->curl->getResponse());
    }

    public function testGetRespJson(): void
    {
        $this->curl->get("https://httpbin.org/json");
        $this->curl->exec();
        $this->assertIsObject($this->curl->getRespJson());
    }

    public function testFind(): void
    {
        $this->curl->get("https://httpbin.org/get");
        $this->curl->exec();
        $this->assertTrue($this->curl->find("X-Amzn-Trace-Id")->result);
        $this->assertTrue($this->curl->find(["X-Amzn-Trace-Id"])->result);
    }

    public function testGetBetween(): void
    {
        $this->curl->get("https://httpbin.org/get");
        $this->curl->exec();
        $this->assertIsString($this->curl->getBetween('"X-Amzn-Trace-Id": "', '"'));
    }

    public function testGetBetweens(): void
    {
        $this->curl->get("https://httpbin.org/get");
        $this->curl->exec();
        $this->assertIsString($this->curl->getBetween('"X-Amzn-Trace-Id": "', '"'));
    }

    public function testGetEffective(): void
    {
        $this->curl->get("https://httpbin.org/get");
        $this->curl->exec();
        $this->assertIsString($this->curl->getEffective());
    }

    public function testGetHttpCode(): void
    {
        $this->curl->get("https://httpbin.org/status/400");
        $this->curl->exec();
        $this->assertIsInt($this->curl->getHttpCode());
        $this->assertEquals(400, $this->curl->getHttpCode());
    }

    public function testGetHeader(): void
    {
        $this->curl->get("https://httpbin.org/response-headers?test_header=val");
        $this->curl->exec();
        $this->assertIsString($this->curl->getHeader('test_header'));
        $this->assertEquals("val", $this->curl->getHeader('test_header'));
    }

    public function testGetHeaders(): void
    {
        $this->curl->get("https://httpbin.org/get");
        $this->curl->exec();
        $this->assertIsArray($this->curl->getHeaders());
    }

    public function testGetCookie(): void
    {
        $this->curl->get("https://httpbin.org/cookies/set/foo/bar");
        $this->curl->exec();
        $this->assertIsString($this->curl->getCookie('foo'));
        $this->assertEquals("bar", $this->curl->getCookie('foo'));
    }

    public function testGetCookiesRaw(): void
    {
        $this->curl->get("https://httpbin.org/cookies/set/foo/bar");
        $this->curl->exec();
        $this->assertIsString($this->curl->getCookiesRaw());
        $this->assertEquals("foo=bar", $this->curl->getCookiesRaw());
    }

    public function testGetCookiesArray(): void
    {
        $this->curl->get("https://httpbin.org/cookies/set/foo/bar");
        $this->curl->exec();
        $this->assertIsArray($this->curl->getCookiesArray());
        $this->assertArrayHasKey('foo', $this->curl->getCookiesArray());
    }
}
