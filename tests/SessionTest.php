<?php

namespace Stilmark\Base\Test;

use PHPUnit\Framework\TestCase;
use Stilmark\Base\Session;

class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testSetAndGet(): void
    {
        Session::set('key', 'value');
        $this->assertEquals('value', Session::get('key'));
    }

    public function testGetReturnsDefaultWhenNotSet(): void
    {
        $this->assertEquals('default', Session::get('nonexistent', 'default'));
    }

    public function testGetReturnsNullWhenNotSetAndNoDefault(): void
    {
        $this->assertNull(Session::get('nonexistent'));
    }

    public function testHas(): void
    {
        $this->assertFalse(Session::has('key'));
        Session::set('key', 'value');
        $this->assertTrue(Session::has('key'));
    }

    public function testRemove(): void
    {
        Session::set('key', 'value');
        $this->assertTrue(Session::has('key'));
        
        Session::remove('key');
        $this->assertFalse(Session::has('key'));
    }

    public function testFlashAndGetFlash(): void
    {
        Session::flash('message', 'Hello World');
        
        // Flash data should exist
        $this->assertEquals('Hello World', Session::getFlash('message'));
        
        // After getting, flash data should be removed
        $this->assertNull(Session::getFlash('message'));
    }

    public function testGetFlashReturnsDefault(): void
    {
        $this->assertEquals('default', Session::getFlash('nonexistent', 'default'));
    }

    public function testUpdateActivity(): void
    {
        Session::updateActivity();
        
        $this->assertTrue(Session::has('last_activity'));
        $this->assertIsInt($_SESSION['last_activity']);
        $this->assertLessThanOrEqual(time(), $_SESSION['last_activity']);
    }

    public function testUpdateActivityWithCustomKey(): void
    {
        Session::updateActivity('custom_activity');
        
        $this->assertTrue(Session::has('custom_activity'));
    }

    public function testSetLoginTime(): void
    {
        Session::setLoginTime();
        
        $this->assertTrue(Session::has('login_time'));
        $this->assertIsInt($_SESSION['login_time']);
    }

    public function testSetLoginTimeWithCustomKey(): void
    {
        Session::setLoginTime('custom_login');
        
        $this->assertTrue(Session::has('custom_login'));
    }

    public function testIsIdleExpiredReturnsTrueWhenNoActivity(): void
    {
        $this->assertTrue(Session::isIdleExpired());
    }

    public function testIsIdleExpiredReturnsFalseWhenRecent(): void
    {
        $_SESSION['last_activity'] = time();
        $this->assertFalse(Session::isIdleExpired());
    }

    public function testIsIdleExpiredReturnsTrueWhenExpired(): void
    {
        $_SESSION['last_activity'] = time() - 50000; // More than default 43200 seconds
        $this->assertTrue(Session::isIdleExpired());
    }

    public function testIsIdleExpiredWithCustomTimeout(): void
    {
        $_SESSION['last_activity'] = time() - 100;
        
        // Should not be expired with 200 second timeout
        $this->assertFalse(Session::isIdleExpired('last_activity', 200));
        
        // Should be expired with 50 second timeout
        $this->assertTrue(Session::isIdleExpired('last_activity', 50));
    }

    public function testIsAbsoluteExpiredReturnsTrueWhenNoLoginTime(): void
    {
        $this->assertTrue(Session::isAbsoluteExpired());
    }

    public function testIsAbsoluteExpiredReturnsFalseWhenRecent(): void
    {
        $_SESSION['login_time'] = time();
        $this->assertFalse(Session::isAbsoluteExpired());
    }

    public function testIsAbsoluteExpiredReturnsTrueWhenExpired(): void
    {
        $_SESSION['login_time'] = time() - 100000; // More than default 86400 seconds
        $this->assertTrue(Session::isAbsoluteExpired());
    }

    public function testIsAbsoluteExpiredWithCustomTimeout(): void
    {
        $_SESSION['login_time'] = time() - 100;
        
        // Should not be expired with 200 second timeout
        $this->assertFalse(Session::isAbsoluteExpired('login_time', 200));
        
        // Should be expired with 50 second timeout
        $this->assertTrue(Session::isAbsoluteExpired('login_time', 50));
    }

    public function testSetStoresVariousTypes(): void
    {
        Session::set('string', 'hello');
        Session::set('int', 42);
        Session::set('array', ['a', 'b']);
        Session::set('bool', true);
        Session::set('null', null);
        
        $this->assertEquals('hello', Session::get('string'));
        $this->assertEquals(42, Session::get('int'));
        $this->assertEquals(['a', 'b'], Session::get('array'));
        $this->assertTrue(Session::get('bool'));
        $this->assertNull(Session::get('null'));
    }
}
