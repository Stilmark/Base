<?php

declare(strict_types=1);

namespace Stilmark\Test\Unit;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Env;

class EnvTest extends TestCase
{
    private string $testEnvPath;
    private string $originalEnvPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test .env file in a temporary directory
        $this->testEnvPath = sys_get_temp_dir() . '/.env.test';
        file_put_contents($this->testEnvPath, "TEST_KEY=test_value\nANOTHER_KEY=another_value");
        
        // Store the original environment variables
        $this->originalEnvPath = getenv('APP_ENV_PATH') ?: '';
    }

    protected function tearDown(): void
    {
        // Clean up the test .env file
        if (file_exists($this->testEnvPath)) {
            unlink($this->testEnvPath);
        }
        
        // Restore the original environment
        if ($this->originalEnvPath) {
            putenv("APP_ENV_PATH={$this->originalEnvPath}");
        } else {
            putenv('APP_ENV_PATH');
        }
        
        parent::tearDown();
    }

    public function testLoadWithCustomPath(): void
    {
        // Test loading from our test .env file
        $result = Env::load($this->testEnvPath);
        
        // Verify the values were loaded
        $this->assertTrue($result, 'load() should return true when environment is loaded successfully');
        $this->assertSame('test_value', Env::get('TEST_KEY'));
        $this->assertSame('another_value', Env::get('ANOTHER_KEY'));
    }
    
    public function testLoadWithEmptyFile(): void
    {
        // Create an empty .env file
        $emptyEnvPath = sys_get_temp_dir() . '/.env.empty';
        file_put_contents($emptyEnvPath, '');
        
        $result = Env::load($emptyEnvPath);
        
        $this->assertFalse($result, 'load() should return false when loading an empty .env file');
        
        // Clean up
        if (file_exists($emptyEnvPath)) {
            unlink($emptyEnvPath);
        }
    }
    
    public function testLoadWithNonExistentFile(): void
    {
        $nonExistentPath = '/path/to/nonexistent/.env';
        $result = Env::load($nonExistentPath);
        
        $this->assertFalse($result, 'load() should return false when .env file does not exist');
    }

    public function testGetWithDefaultValue(): void
    {
        // Test getting a non-existent key with a default value
        $this->assertSame('default_value', Env::get('NON_EXISTENT_KEY', 'default_value'));
    }

    public function testSetAndGet(): void
    {
        // Test setting and getting a value
        Env::set('CUSTOM_KEY', 'custom_value');
        $this->assertSame('custom_value', Env::get('CUSTOM_KEY'));
    }
}
