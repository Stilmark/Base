<?php

declare(strict_types=1);

namespace Stilmark\Test\Unit;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Request;

class RequestTest extends TestCase
{
    private array $serverBackup;
    private array $getBackup;
    private array $postBackup;
    private array $cookieBackup;
    private array $filesBackup;

    protected function setUp(): void
    {
        // Backup superglobals
        $this->serverBackup = $_SERVER;
        $this->getBackup = $_GET;
        $this->postBackup = $_POST;
        $this->cookieBackup = $_COOKIE;
        $this->filesBackup = $_FILES;
    }

    protected function tearDown(): void
    {
        // Restore superglobals
        $_SERVER = $this->serverBackup;
        $_GET = $this->getBackup;
        $_POST = $this->postBackup;
        $_COOKIE = $this->cookieBackup;
        $_FILES = $this->filesBackup;
    }

    private function createRequest(
        array $get = [],
        array $post = [],
        array $server = [],
        array $cookies = [],
        array $files = [],
        ?string $input = null
    ): Request {
        // Set up the request data
        $request = new Request();
        
        // Use reflection to set private properties
        $reflection = new \ReflectionClass($request);
        
        // Set input property
        if ($input !== null) {
            $inputProperty = $reflection->getProperty('input');
            $inputProperty->setAccessible(true);
            $inputProperty->setValue($request, json_decode($input, true) ?? []);
        }
        
        // Set other properties
        $getProperty = $reflection->getProperty('get');
        $getProperty->setAccessible(true);
        $getProperty->setValue($request, $get);
        
        $postProperty = $reflection->getProperty('post');
        $postProperty->setAccessible(true);
        $postProperty->setValue($request, $post);
        
        $cookiesProperty = $reflection->getProperty('cookies');
        $cookiesProperty->setAccessible(true);
        $cookiesProperty->setValue($request, $cookies);
        
        $filesProperty = $reflection->getProperty('files');
        $filesProperty->setAccessible(true);
        $filesProperty->setValue($request, $files);
        
        $serverProperty = $reflection->getProperty('server');
        $serverProperty->setAccessible(true);
        $serverProperty->setValue($request, array_merge($_SERVER, $server));
        
        $headersProperty = $reflection->getProperty('headers');
        $headersProperty->setAccessible(true);
        $headersProperty->setValue($request, $this->getAllHeaders($server));
        
        return $request;
    }
    
    private function getAllHeaders(array $serverVars = []): array
    {
        $headers = [];
        $server = array_merge($_SERVER, $serverVars);
        
        foreach ($server as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            }
        }
        
        return $headers;
    }

    private function setPrivateProperty(string $property, $value): void
    {
        $reflection = new \ReflectionClass('Stilmark\\Base\\Request');
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($this->createRequest(), $value);
    }

    public function testAllMethodReturnsMergedData(): void
    {
        $request = $this->createRequest(
            ['get_key' => 'get_value'],
            ['post_key' => 'post_value'],
            [],
            [],
            [],
            json_encode(['json_key' => 'json_value'])
        );

        $result = $request->all();

        $this->assertArrayHasKey('get_key', $result);
        $this->assertArrayHasKey('post_key', $result);
        $this->assertArrayHasKey('json_key', $result);
        $this->assertSame('get_value', $result['get_key']);
        $this->assertSame('post_value', $result['post_key']);
        $this->assertSame('json_value', $result['json_key']);
    }

    public function testQueryMethodReturnsValueFromAll(): void
    {
        $request = $this->createRequest(
            ['key' => 'get_value'],
            ['key' => 'post_value']
        );

        // Should prioritize GET over POST
        $this->assertSame('get_value', $request->query('key'));
        $this->assertSame('default', $request->query('nonexistent', 'default'));
    }

    public function testGetMethodReturnsGetValue(): void
    {
        $request = $this->createRequest(['key' => 'value']);
        
        $this->assertSame('value', $request->get('key'));
        $this->assertNull($request->get('nonexistent'));
        $this->assertSame('default', $request->get('nonexistent', 'default'));
    }

    public function testPostMethodReturnsPostValue(): void
    {
        $request = $this->createRequest(
            [],
            ['key' => 'post_value']
        );
        
        $this->assertSame('post_value', $request->post('key'));
        $this->assertNull($request->post('nonexistent'));
        $this->assertSame('default', $request->post('nonexistent', 'default'));
    }

    public function testServerMethodReturnsServerValue(): void
    {
        $server = ['HTTP_USER_AGENT' => 'TestAgent'];
        $request = $this->createRequest([], [], $server);
        
        $this->assertSame('TestAgent', $request->server('HTTP_USER_AGENT'));
        $this->assertNull($request->server('NON_EXISTENT_HEADER'));
        $this->assertSame('default', $request->server('NON_EXISTENT_HEADER', 'default'));
    }

    public function testHeaderMethodReturnsHeaderValue(): void
    {
        $server = ['HTTP_X_CUSTOM_HEADER' => 'custom_value'];
        $request = $this->createRequest([], [], $server);
        
        $this->assertSame('custom_value', $request->header('x-custom-header'));
        $this->assertSame('custom_value', $request->header('X-Custom-Header'));
        $this->assertNull($request->header('non-existent'));
        $this->assertSame('default', $request->header('non-existent', 'default'));
    }

    public function testCookieMethodReturnsCookieValue(): void
    {
        $cookies = ['session_id' => 'abc123'];
        $request = $this->createRequest([], [], [], $cookies);
        
        $this->assertSame('abc123', $request->cookie('session_id'));
        $this->assertNull($request->cookie('nonexistent'));
        $this->assertSame('default', $request->cookie('nonexistent', 'default'));
    }

    public function testFileMethodReturnsFileValue(): void
    {
        $files = [
            'file' => [
                'name' => 'test.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/php123.tmp',
                'error' => UPLOAD_ERR_OK,
                'size' => 123
            ]
        ];
        
        $request = $this->createRequest([], [], [], [], $files);
        
        $file = $request->file('file');
        $this->assertIsArray($file);
        $this->assertSame('test.txt', $file['name']);
        $this->assertNull($request->file('nonexistent'));
        $this->assertSame('default', $request->file('nonexistent', 'default'));
    }

    public function testInputMethodReturnsParsedJson(): void
    {
        $json = json_encode(['key' => 'value', 'nested' => ['a' => 'b']]);
        $request = $this->createRequest([], [], [], [], [], $json);
        
        $input = $this->getPrivatePropertyValue($request, 'input');
        $this->assertSame(['key' => 'value', 'nested' => ['a' => 'b']], $input);
    }

    private function getPrivatePropertyValue($object, string $property)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
