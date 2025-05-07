<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        // セッションをモックする
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }
    
    protected function tearDown(): void
    {
        // セッション変数をクリアする
        $_SESSION = [];
    }
    
    public function testIsLoggedInReturnsTrueWhenUserIdExists()
    {
        // ユーザーIDがセットされている場合
        $_SESSION['user_id'] = 1;
        $this->assertTrue(\isLoggedIn());
    }
    
    public function testIsLoggedInReturnsFalseWhenUserIdDoesNotExist()
    {
        // ユーザーIDがセットされていない場合
        unset($_SESSION['user_id']);
        $this->assertFalse(\isLoggedIn());
    }
    
    public function testGetCurrentUserIdReturnsCorrectId()
    {
        // ユーザーIDがセットされている場合
        $_SESSION['user_id'] = 42;
        $this->assertEquals(42, \getCurrentUserId());
    }
    
    public function testGetCurrentUserIdReturnsNullWhenNotLoggedIn()
    {
        // ユーザーIDがセットされていない場合
        unset($_SESSION['user_id']);
        $this->assertNull(\getCurrentUserId());
    }
} 