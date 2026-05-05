<?php

namespace Stilmark\Base\Test;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Helper;

class HelperTest extends TestCase
{
    // String conversion tests

    public function testCamelToSnakeBasic(): void
    {
        $this->assertEquals('hello_world', Helper::camelToSnake('helloWorld'));
    }

    public function testCamelToSnakeMultipleWords(): void
    {
        $this->assertEquals('user_profile_settings', Helper::camelToSnake('userProfileSettings'));
    }

    public function testCamelToSnakeSingleWord(): void
    {
        $this->assertEquals('hello', Helper::camelToSnake('hello'));
    }

    public function testCamelToSnakeAlreadySnakeCase(): void
    {
        $this->assertEquals('hello_world', Helper::camelToSnake('hello_world'));
    }

    public function testCamelToSnakeEmptyString(): void
    {
        $this->assertEquals('', Helper::camelToSnake(''));
    }

    public function testSnakeToCamelBasic(): void
    {
        $this->assertEquals('HelloWorld', Helper::snakeToCamel('hello_world'));
    }

    public function testSnakeToCamelMultipleWords(): void
    {
        $this->assertEquals('ThisIsATest', Helper::snakeToCamel('this_is_a_test'));
    }

    public function testSnakeToCamelSingleWord(): void
    {
        $this->assertEquals('Hello', Helper::snakeToCamel('hello'));
    }

    public function testSnakeToCamelEmptyString(): void
    {
        $this->assertEquals('', Helper::snakeToCamel(''));
    }

    // Array key conversion tests

    public function testArrayKeysCamelToSnakeFlat(): void
    {
        $input = ['firstName' => 'John', 'lastName' => 'Doe'];
        $expected = ['first_name' => 'John', 'last_name' => 'Doe'];
        
        $this->assertEquals($expected, Helper::arrayKeysCamelToSnake($input));
    }

    public function testArrayKeysCamelToSnakeNested(): void
    {
        $input = [
            'userName' => 'john',
            'userDetails' => [
                'firstName' => 'John',
                'lastName' => 'Doe'
            ]
        ];
        $expected = [
            'user_name' => 'john',
            'user_details' => [
                'first_name' => 'John',
                'last_name' => 'Doe'
            ]
        ];
        
        $this->assertEquals($expected, Helper::arrayKeysCamelToSnake($input));
    }

    public function testArrayKeysCamelToSnakeEmptyArray(): void
    {
        $this->assertEquals([], Helper::arrayKeysCamelToSnake([]));
    }

    public function testArrayKeysCamelToSnakePreservesValues(): void
    {
        $input = ['someKey' => 123, 'anotherKey' => true, 'thirdKey' => null];
        $result = Helper::arrayKeysCamelToSnake($input);
        
        $this->assertEquals(123, $result['some_key']);
        $this->assertTrue($result['another_key']);
        $this->assertNull($result['third_key']);
    }

    // Cookie helper tests

    public function testGetCookieReturnsValue(): void
    {
        $_COOKIE['test_cookie'] = 'cookie_value';
        $this->assertEquals('cookie_value', Helper::getCookie('test_cookie'));
        unset($_COOKIE['test_cookie']);
    }

    public function testGetCookieReturnsDefaultWhenNotSet(): void
    {
        $this->assertEquals('default', Helper::getCookie('nonexistent', 'default'));
    }

    public function testGetCookieReturnsNullWhenNotSetAndNoDefault(): void
    {
        $this->assertNull(Helper::getCookie('nonexistent'));
    }
}
