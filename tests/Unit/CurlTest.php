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

    public function testGetCurlError(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetDefault(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetMethod(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
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

    public function testExec(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetOpt(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetInfo(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetDebug(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetCookieFile(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetCookieJar(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetFollow(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetReturn(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetAutoReferer(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetAutoReferrer(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetTimeout(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetConnectTimeout(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetMaxConnect(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetMaxRedirect(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetProxy(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetProxyType(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetProxyAuth(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetDns(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetResponse(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetRespJson(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFind(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetBetween(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetBetweens(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetEffective(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetHttpCode(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetHeader(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetHeaders(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetCookie(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetCookiesRaw(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetCookiesArray(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
