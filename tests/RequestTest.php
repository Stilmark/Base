<?php

namespace Stilmark\Base\Test;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Request;

class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
        $_COOKIE = [];
        $_FILES = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
        $_COOKIE = [];
        $_FILES = [];
    }

    // Basic request data tests

    public function testQueryReturnsGetParameter(): void
    {
        $_GET['name'] = 'John';
        $request = new Request();
        
        $this->assertEquals('John', $request->query('name'));
    }

    public function testQueryReturnsPostParameter(): void
    {
        $_POST['name'] = 'John';
        $request = new Request();
        
        $this->assertEquals('John', $request->query('name'));
    }

    public function testQueryGetTakesPrecedenceOverPost(): void
    {
        $_GET['name'] = 'GetValue';
        $_POST['name'] = 'PostValue';
        $request = new Request();
        
        $this->assertEquals('GetValue', $request->query('name'));
    }

    public function testQueryReturnsDefault(): void
    {
        $request = new Request();
        $this->assertEquals('default', $request->query('nonexistent', 'default'));
    }

    public function testQueryReturnsJsonInput(): void
    {
        $request = new Request(['name' => 'JsonValue']);
        $this->assertEquals('JsonValue', $request->query('name'));
    }

    public function testGetMethod(): void
    {
        $_GET['key'] = 'value';
        $request = new Request();
        
        $this->assertEquals('value', $request->get('key'));
        $this->assertNull($request->get('nonexistent'));
        $this->assertEquals('default', $request->get('nonexistent', 'default'));
    }

    public function testPostMethod(): void
    {
        $_POST['key'] = 'value';
        $request = new Request();
        
        $this->assertEquals('value', $request->post('key'));
        $this->assertNull($request->post('nonexistent'));
    }

    public function testJsonMethod(): void
    {
        $request = new Request(['key' => 'value', 'nested' => ['a' => 1]]);
        
        $this->assertEquals('value', $request->json('key'));
        $this->assertEquals(['key' => 'value', 'nested' => ['a' => 1]], $request->json());
        $this->assertNull($request->json('nonexistent'));
    }

    public function testAllMergesAllSources(): void
    {
        $_GET['get_key'] = 'get_value';
        $_POST['post_key'] = 'post_value';
        $request = new Request(['json_key' => 'json_value']);
        
        $all = $request->all();
        
        $this->assertEquals('get_value', $all['get_key']);
        $this->assertEquals('post_value', $all['post_key']);
        $this->assertEquals('json_value', $all['json_key']);
    }

    // Has methods tests

    public function testHasGetWithSingleKey(): void
    {
        $_GET['name'] = 'John';
        $request = new Request();
        
        $result = $request->hasGet('name');
        $this->assertEquals(['name' => 'John'], $result);
    }

    public function testHasGetWithMultipleKeys(): void
    {
        $_GET['name'] = 'John';
        $_GET['email'] = 'john@example.com';
        $request = new Request();
        
        $result = $request->hasGet(['name', 'email']);
        $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $result);
    }

    public function testHasGetReturnsFalseWhenKeyMissing(): void
    {
        $_GET['name'] = 'John';
        $request = new Request();
        
        $this->assertFalse($request->hasGet(['name', 'missing']));
    }

    public function testHasPostWithSingleKey(): void
    {
        $_POST['name'] = 'John';
        $request = new Request();
        
        $result = $request->hasPost('name');
        $this->assertEquals(['name' => 'John'], $result);
    }

    public function testHasJsonWithSingleKey(): void
    {
        $request = new Request(['name' => 'John']);
        
        $result = $request->hasJson('name');
        $this->assertEquals(['name' => 'John'], $result);
    }

    public function testHasJsonReturnsFalseWhenNotArray(): void
    {
        $request = new Request();
        $this->assertFalse($request->hasJson('name'));
    }

    // Server and header tests

    public function testServerMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request();
        
        $this->assertEquals('POST', $request->server('REQUEST_METHOD'));
        $this->assertNull($request->server('NONEXISTENT'));
    }

    public function testHeaderMethod(): void
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $request = new Request();
        
        $this->assertEquals('application/json', $request->header('Content-Type'));
    }

    public function testMethodReturnsRequestMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request();
        
        $this->assertEquals('POST', $request->method());
    }

    public function testMethodDefaultsToGet(): void
    {
        $request = new Request();
        $this->assertEquals('GET', $request->method());
    }

    public function testUriReturnsRequestUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/users';
        $request = new Request();
        
        $this->assertEquals('/api/users', $request->uri());
    }

    public function testIsAjax(): void
    {
        $request = new Request();
        $this->assertFalse($request->isAjax());
        
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = new Request();
        $this->assertTrue($request->isAjax());
    }

    // Cookie and file tests

    public function testCookieMethod(): void
    {
        $_COOKIE['session_id'] = 'abc123';
        $request = new Request();
        
        $this->assertEquals('abc123', $request->cookie('session_id'));
        $this->assertNull($request->cookie('nonexistent'));
    }

    public function testFileMethod(): void
    {
        $_FILES['upload'] = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/phpXXX',
            'error' => UPLOAD_ERR_OK,
            'size' => 100
        ];
        $request = new Request();
        
        $file = $request->file('upload');
        $this->assertEquals('test.txt', $file['name']);
        $this->assertNull($request->file('nonexistent'));
    }

    // Sanitization tests

    public function testSanitize(): void
    {
        $request = new Request();
        
        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', 
            $request->sanitize('<script>alert("xss")</script>'));
        $this->assertEquals('Hello World', $request->sanitize('Hello World'));
    }

    public function testSafeMethod(): void
    {
        $_GET['input'] = '<script>alert("xss")</script>';
        $request = new Request();
        
        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', 
            $request->safe('input'));
    }

    public function testEmailValidation(): void
    {
        $_GET['email'] = 'test@example.com';
        $request = new Request();
        
        $this->assertEquals('test@example.com', $request->email('email'));
    }

    public function testEmailValidationReturnsDefaultForInvalid(): void
    {
        $_GET['email'] = 'invalid-email';
        $request = new Request();
        
        $this->assertNull($request->email('email'));
        $this->assertEquals('default@example.com', $request->email('email', 'default@example.com'));
    }

    // Validation tests

    public function testValidatePostRequired(): void
    {
        $_POST['name'] = 'John';
        $request = new Request();
        
        $result = $request->validatePost(['name' => 'required']);
        
        $this->assertTrue($result['valid']);
        $this->assertEquals('John', $result['data']['name']);
        $this->assertEmpty($result['errors']);
    }

    public function testValidatePostRequiredFails(): void
    {
        $_POST['name'] = '';
        $request = new Request();
        
        $result = $request->validatePost(['name' => 'required']);
        
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('name', $result['errors']);
    }

    public function testValidatePostEmail(): void
    {
        $_POST['email'] = 'test@example.com';
        $request = new Request();
        
        $result = $request->validatePost(['email' => 'email']);
        
        $this->assertTrue($result['valid']);
        $this->assertEquals('test@example.com', $result['data']['email']);
    }

    public function testValidatePostEmailFails(): void
    {
        $_POST['email'] = 'invalid-email';
        $request = new Request();
        
        $result = $request->validatePost(['email' => 'required|email']);
        
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function testValidatePostMinLength(): void
    {
        $_POST['password'] = 'short';
        $request = new Request();
        
        $result = $request->validatePost(['password' => 'min:8']);
        
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('password', $result['errors']);
    }

    public function testValidatePostMaxLength(): void
    {
        $_POST['username'] = 'verylongusernamethatexceedslimit';
        $request = new Request();
        
        $result = $request->validatePost(['username' => 'max:20']);
        
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('username', $result['errors']);
    }

    public function testValidatePostUrl(): void
    {
        $_POST['website'] = 'https://example.com';
        $request = new Request();
        
        $result = $request->validatePost(['website' => 'url']);
        
        $this->assertTrue($result['valid']);
    }

    public function testValidatePostType(): void
    {
        $_POST['age'] = '25';
        $request = new Request();
        
        $result = $request->validatePost(['age' => 'type:int']);
        
        $this->assertTrue($result['valid']);
    }

    public function testValidateJsonRules(): void
    {
        $request = new Request(['name' => 'John', 'email' => 'john@example.com']);
        
        $result = $request->validateJson([
            'name' => 'required',
            'email' => 'required|email'
        ]);
        
        $this->assertTrue($result['valid']);
    }

    public function testValidateGetRules(): void
    {
        $_GET['page'] = '1';
        $request = new Request();
        
        $result = $request->validateGet(['page' => 'type:int']);
        
        $this->assertTrue($result['valid']);
    }

    // Unsafe method tests

    public function testIsUnsafeMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        $this->assertFalse($request->isUnsafeMethod());
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request();
        $this->assertTrue($request->isUnsafeMethod());
        
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = new Request();
        $this->assertTrue($request->isUnsafeMethod());
        
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $request = new Request();
        $this->assertTrue($request->isUnsafeMethod());
    }
}
