<?php

namespace Stilmark\Base\Test;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Env;

class EnvTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear environment variables before each test
        putenv('TEST_VAR');
        unset($_SERVER['TEST_VAR'], $_ENV['TEST_VAR']);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        putenv('TEST_VAR');
        unset($_SERVER['TEST_VAR'], $_ENV['TEST_VAR']);
    }

    public function testGetReturnsDefaultWhenVariableNotSet(): void
    {
        $result = Env::get('NONEXISTENT_VAR', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function testGetReturnsNullWhenNoDefault(): void
    {
        $result = Env::get('NONEXISTENT_VAR');
        $this->assertNull($result);
    }

    public function testGetReturnsValueFromGetenv(): void
    {
        putenv('TEST_VAR=getenv_value');
        $result = Env::get('TEST_VAR');
        $this->assertEquals('getenv_value', $result);
    }

    public function testGetReturnsValueFromServer(): void
    {
        $_SERVER['TEST_VAR'] = 'server_value';
        $result = Env::get('TEST_VAR');
        $this->assertEquals('server_value', $result);
    }

    public function testGetReturnsValueFromEnv(): void
    {
        $_ENV['TEST_VAR'] = 'env_value';
        $result = Env::get('TEST_VAR');
        $this->assertEquals('env_value', $result);
    }

    public function testGetenvTakesPrecedenceOverServer(): void
    {
        putenv('TEST_VAR=getenv_value');
        $_SERVER['TEST_VAR'] = 'server_value';
        $result = Env::get('TEST_VAR');
        $this->assertEquals('getenv_value', $result);
    }

    public function testSetSetsValueInAllLocations(): void
    {
        Env::set('TEST_VAR', 'set_value');
        
        $this->assertEquals('set_value', getenv('TEST_VAR'));
        $this->assertEquals('set_value', $_SERVER['TEST_VAR']);
        $this->assertEquals('set_value', $_ENV['TEST_VAR']);
    }

    public function testSetOverwritesExistingValue(): void
    {
        Env::set('TEST_VAR', 'initial_value');
        Env::set('TEST_VAR', 'updated_value');
        
        $this->assertEquals('updated_value', Env::get('TEST_VAR'));
    }
}
