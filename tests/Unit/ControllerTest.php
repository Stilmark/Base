<?php

declare(strict_types=1);

namespace Stilmark\Test\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stilmark\Base\Controller;
use Stilmark\Base\Request;

class TestController extends Controller
{
    public bool $initialized = false;
    
    protected function initialize(): void
    {
        $this->initialized = true;
    }
    
    public function getRequest(): Request
    {
        return $this->request;
    }
    
    public function testJson($data, int $statusCode = 200): array
    {
        // Mock the exit call
        $mockExit = function() {};
        
        // Start output buffering
        ob_start();
        
        // Call the actual json method with a mock for exit
        $this->mockFunction('exit', $mockExit);
        parent::json($data, $statusCode);
        
        // Get the output and clean the buffer
        $output = ob_get_clean();
        
        // Get the response code
        $responseCode = http_response_code();
        
        return [
            'output' => $output,
            'status_code' => $responseCode,
            'headers' => $this->getResponseHeaders()
        ];
    }
    
    private function mockFunction(string $functionName, callable $mock): void
    {
        $namespace = __NAMESPACE__;
        $function = "{$namespace}\\{$functionName}";
        
        if (!function_exists($function)) {
            eval("function {$function}(...\$args) use (\$mock) { return \$mock(...\$args); }");
        }
    }
    
    private function getResponseHeaders(): array
    {
        $headers = [];
        foreach (headers_list() as $header) {
            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $headers[trim($parts[0])] = trim($parts[1]);
            }
        }
        return $headers;
    }
    
    public function testRedirect(string $url, int $statusCode = 302): array
    {
        $headers = [];
        $this->mockHeaderFunction($headers);
        
        // Mock the exit call
        $this->mockFunction('exit', function() {});
        
        // Call the parent redirect method
        parent::redirect($url, $statusCode);
        
        return $headers;
    }
    
    private function mockHeaderFunction(array &$headers): void
    {
        global $mockHeaders;
        $mockHeaders = &$headers;
        
        if (!function_exists('Stilmark\Test\Unit\header')) {
            function header(string $header, bool $replace = true, ?int $response_code = null): void
            {
                global $mockHeaders;
                $mockHeaders[] = [
                    'header' => $header,
                    'replace' => $replace,
                    'code' => $response_code
                ];
            }
        }
    }
}

class ControllerTest extends TestCase
{
    private TestController $controller;
    private Request $request;
    
    protected function setUp(): void
    {
        $this->request = new Request();
        $this->controller = new TestController();
    }
    
    public function testConstructorInitializesRequest(): void
    {
        $this->assertInstanceOf(Request::class, $this->controller->getRequest());
    }
    
    public function testInitializeMethodIsCalled(): void
    {
        $this->assertTrue($this->controller->initialized);
    }
    
    public function testJsonOutput(): void
    {
        $data = ['test' => 'value'];
        $result = $this->controller->testJson($data, 201);
        
        $this->assertEquals('{"test":"value"}', $result['output']);
        $this->assertEquals(201, $result['status_code']);
        $this->assertArrayHasKey('Content-Type', $result['headers']);
        $this->assertEquals('application/json', $result['headers']['Content-Type']);
    }
    
    public function testRedirectSetsHeaders(): void
    {
        $url = 'https://example.com';
        $statusCode = 301;
        
        $headers = $this->controller->testRedirect($url, $statusCode);
        
        $this->assertCount(1, $headers);
        $this->assertEquals("Location: $url", $headers[0]['header']);
        $this->assertEquals($statusCode, $headers[0]['code']);
    }
    
    protected function tearDown(): void
    {
        // Reset headers
        if (function_exists('header_remove')) {
            header_remove();
        }
    }
}
