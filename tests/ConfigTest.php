<?php

namespace Tests;

use Leeqvip\Apollo\Config;
use PHPUnit\Framework\TestCase;

/**
 * Config测试
 */
class ConfigTest extends TestCase
{
    /**
     * 测试获取和设置配置
     */
    public function testGetAndSet()
    {
        $config = new Config();

        // 设置配置
        $config->set('test.key', 'test.value');

        // 获取配置
        $this->assertEquals('test.value', $config->get('test.key'));

        // 获取不存在的配置，返回默认值
        $this->assertEquals('default', $config->get('non.existent', 'default'));
    }

    /**
     * 测试批量设置配置
     */
    public function testSetBatch()
    {
        $config = new Config();

        // 批量设置配置
        $config->setBatch([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        // 获取配置
        $this->assertEquals('value1', $config->get('key1'));
        $this->assertEquals('value2', $config->get('key2'));
    }

    /**
     * 测试命名空间
     */
    public function testNamespace()
    {
        $config = new Config();

        // 设置不同命名空间的配置
        $config->set('key', 'value1', 'ns1');
        $config->set('key', 'value2', 'ns2');

        // 获取不同命名空间的配置
        $this->assertEquals('value1', $config->get('key', null, 'ns1'));
        $this->assertEquals('value2', $config->get('key', null, 'ns2'));
    }

    /**
     * 测试配置变更回调
     */
    public function testCallback()
    {
        $config = new Config();
        $called = false;
        $data = [];

        // 注册回调
        $config->registerCallback(function ($configs) use (&$called, &$data) {
            $called = true;
            $data = $configs;
        });

        // 批量设置配置，触发回调
        $config->setBatch(['key' => 'value']);

        // 验证回调被调用
        $this->assertTrue($called);
        $this->assertEquals(['key' => 'value'], $data);
    }
}
