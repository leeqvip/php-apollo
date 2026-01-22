<?php

namespace Tests;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Leeqvip\Apollo\Client;
use Leeqvip\Apollo\Exceptions\ApolloException;

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
        Client::fakeClear();
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
        Client::fakeClear();
        Client::fake('*', [], new Response(200, [], '{}'));
        $response = Client::fakeGet('/configs/default');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{}', $response->getBody()->getContents());
    }
}
