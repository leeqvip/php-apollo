<?php

namespace Tests;

use GuzzleHttp\Psr7\Response;
use Leeqvip\Apollo\Apollo;
use Leeqvip\Apollo\Client;

class TestCase extends \PHPUnit\Framework\TestCase
{
    private static string $appId = "testAppId";

    private static string $cluster = "testCluster";

    private static string $serverUrl = "http://localhost:8080";

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        Client::fake('/configfiles/json/*', [], new Response(200, [], json_encode([
            'appId' => self::$appId,
            'cluster' => self::$cluster,
            'namespaceName' => self::$cluster,
            'configurations' => [
                'APP_ENV' => 'default',
                'APP_DEBUG' => 'false',
            ],
            'releaseKey' => '20260121165210-970a2ab79cbd44e7'
        ])));

        Client::fake('/configs/*', [], new Response(200, [], json_encode([
            'appId' => self::$appId,
            'cluster' => self::$cluster,
            'namespaceName' => self::$cluster,
            'configurations' => [
                'APP_ENV' => 'default',
                'APP_DEBUG' => 'false',
            ],
            'releaseKey' => '20260121165210-970a2ab79cbd44e7'
        ])));

        // 获取实例
        $instance = Apollo::getInstance([
            'app_id' => self::$appId,
            'cluster' => self::$cluster,
            'server_url' => 'http://localhost:8080',
        ]);
        $instance->removeCache();
    }
}