<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Database;

class DatabaseTest extends TestCase
{
    public function testSingletonInstance()
    {
        // 同じインスタンスが返されることを確認
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assertSame($db1, $db2, 'Database should return same instance');
    }
    
    public function testConnectionIsValid()
    {
        // 接続が有効なPDOインスタンスであることを確認
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $this->assertInstanceOf(\PDO::class, $conn);
        
        // 簡単なクエリが実行できることを確認
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        $this->assertEquals(1, $result['test']);
    }
} 