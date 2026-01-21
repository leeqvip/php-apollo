<?php

namespace Tests;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Leeqvip\Apollo\Client;
use Leeqvip\Apollo\Exceptions\ApolloException;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private string $appId = "testAppId";

    private string $cluster = "testCluster";

    /**
     * @throws ApolloException
     * @throws GuzzleException
     */
    public function testGetConfigImmediately(): void
    {
        Client::fake('*', [], new Response(200, [], json_encode([
            'appId' => $this->appId,
            'cluster' => $this->cluster,
            'namespaceName' => $this->cluster,
            'configurations' => [
                'APP_ENV' => 'default',
                'APP_DEBUG' => 'false',
            ],
            'releaseKey' => '20260121165210-970a2ab79cbd44e7'
        ])));

        $client = new Client([
            'app_id' => $this->appId,
            'cluster' => $this->cluster
        ]);
        $namespace = "testNamespace";
        $config = $client->getConfigImmediately($namespace);
        $this->assertIsArray($config);
        $this->assertEquals('default', $config["APP_ENV"]);
        $this->assertEquals('false', $config["APP_DEBUG"]);
    }

    public function testGetConfig(): void
    {
        $body = json_encode([
            'APP_ENV' => 'default',
            'APP_DEBUG' => 'false',
        ]);
        Client::fake('*', [], new Response(200, [], $body));

        $client = new Client([
            'app_id' => $this->appId,
            'cluster' => $this->cluster
        ]);
        $namespace = "testNamespace";
        $config = $client->getConfig($namespace);
        $this->assertIsString($config);
        $this->assertEquals($body, $config);
    }

    public function testFakeGet(): void
    {
        Client::fake('*', [], new Response(200, [], '{}'));
        $response = Client::fakeGet('/configs/default');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{}', $response->getBody()->getContents());
    }
}
