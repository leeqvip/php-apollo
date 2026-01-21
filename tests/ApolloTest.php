<?php

namespace Tests;

use Leeqvip\Apollo\Apollo;
use PHPUnit\Framework\TestCase;

/**
 * Apollo测试
 */
class ApolloTest extends TestCase
{
    /**
     * 测试单例模式
     */
    public function testSingleton()
    {
        $config = [
            'app_id' => 'test',
            'server_url' => 'http://localhost:8080',
        ];

        // 获取实例
        $instance1 = Apollo::getInstance($config);
        $instance2 = Apollo::getInstance($config);

        // 验证是同一个实例
        $this->assertSame($instance1, $instance2);
    }

    /**
     * 测试获取配置
     */
    public function testGetConfig()
    {
        $config = [
            'app_id' => 'test',
            'server_url' => 'http://localhost:8080',
        ];

        // 获取实例
        $apollo = Apollo::getInstance($config);

        // 测试获取配置（这里会返回默认值，因为没有实际的Apollo服务器）
        $this->assertNull($apollo->get('test.key'));
        $this->assertEquals('default', $apollo->get('test.key', 'default'));
    }
}
