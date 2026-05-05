<?php

namespace Stilmark\Base\Test;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Logger;
use Stilmark\Base\Env;
use ReflectionClass;

class LoggerTest extends TestCase
{
    private string $testLogPath;

    protected function setUp(): void
    {
        $this->testLogPath = sys_get_temp_dir() . '/base_test_logs_' . uniqid();
        Env::set('LOG_PATH', $this->testLogPath);
        
        // Reset static logPath property
        $reflection = new ReflectionClass(Logger::class);
        $property = $reflection->getProperty('logPath');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    protected function tearDown(): void
    {
        // Clean up test log files
        if (is_dir($this->testLogPath)) {
            $files = glob($this->testLogPath . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->testLogPath);
        }
        
        // Reset static logPath property
        $reflection = new ReflectionClass(Logger::class);
        $property = $reflection->getProperty('logPath');
        $property->setAccessible(true);
        $property->setValue(null, null);
        
        putenv('LOG_PATH');
    }

    public function testInitCreatesLogDirectory(): void
    {
        $this->assertDirectoryDoesNotExist($this->testLogPath);
        Logger::init();
        $this->assertDirectoryExists($this->testLogPath);
    }

    public function testLogCreatesAppLogFile(): void
    {
        Logger::init();
        Logger::log('Test message');
        
        $logFile = $this->testLogPath . '/app.log';
        $this->assertFileExists($logFile);
    }

    public function testLogWritesJsonFormat(): void
    {
        Logger::init();
        Logger::log('Test message', 'info');
        
        $logFile = $this->testLogPath . '/app.log';
        $content = file_get_contents($logFile);
        $decoded = json_decode(trim($content), true);
        
        $this->assertIsArray($decoded);
        $this->assertEquals('Test message', $decoded['message']);
        $this->assertEquals('INFO', $decoded['level']);
        $this->assertArrayHasKey('timestamp', $decoded);
    }

    public function testLogWithData(): void
    {
        Logger::init();
        Logger::log('Test message', 'info', ['key' => 'value']);
        
        $logFile = $this->testLogPath . '/app.log';
        $content = file_get_contents($logFile);
        $decoded = json_decode(trim($content), true);
        
        $this->assertArrayHasKey('data', $decoded);
        $this->assertEquals('value', $decoded['data']['key']);
    }

    public function testLogLevelValidation(): void
    {
        Logger::init();
        
        // Valid levels
        $validLevels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];
        
        foreach ($validLevels as $level) {
            // Clear log file
            $logFile = $this->testLogPath . '/app.log';
            if (file_exists($logFile)) {
                unlink($logFile);
            }
            
            Logger::log('Test', $level);
            $content = file_get_contents($logFile);
            $decoded = json_decode(trim($content), true);
            
            $this->assertEquals(strtoupper($level), $decoded['level']);
        }
    }

    public function testLogInvalidLevelDefaultsToInfo(): void
    {
        Logger::init();
        Logger::log('Test message', 'invalid_level');
        
        $logFile = $this->testLogPath . '/app.log';
        $content = file_get_contents($logFile);
        $decoded = json_decode(trim($content), true);
        
        $this->assertEquals('INFO', $decoded['level']);
    }

    public function testLogReturnsTrue(): void
    {
        Logger::init();
        $result = Logger::log('Test message');
        $this->assertTrue($result);
    }

    public function testLogAppendsToFile(): void
    {
        Logger::init();
        Logger::log('First message');
        Logger::log('Second message');
        
        $logFile = $this->testLogPath . '/app.log';
        $content = file_get_contents($logFile);
        $lines = array_filter(explode(PHP_EOL, $content));
        
        $this->assertCount(2, $lines);
    }

    public function testLogWithUserSession(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $_SESSION['user'] = [
            'id' => 123,
            'email' => 'test@example.com'
        ];
        
        Logger::init();
        Logger::log('Test message');
        
        $logFile = $this->testLogPath . '/app.log';
        $content = file_get_contents($logFile);
        $decoded = json_decode(trim($content), true);
        
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('person', $decoded['data']);
        $this->assertEquals(123, $decoded['data']['person']['id']);
        $this->assertEquals('test@example.com', $decoded['data']['person']['email']);
        
        unset($_SESSION['user']);
    }

    public function testLogWithoutData(): void
    {
        Logger::init();
        Logger::log('Test message');
        
        $logFile = $this->testLogPath . '/app.log';
        $content = file_get_contents($logFile);
        $decoded = json_decode(trim($content), true);
        
        // When no data is provided and no session user, data should not be in output
        $this->assertArrayNotHasKey('data', $decoded);
    }
}
