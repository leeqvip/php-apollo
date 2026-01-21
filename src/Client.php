<?php

declare(strict_types=1);

namespace Leeqvip\Apollo;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Leeqvip\Apollo\Auths\Auth;
use Leeqvip\Apollo\Exceptions\ApolloException;
use Leeqvip\Apollo\Traits\FakeClient;
use Psr\Http\Message\ResponseInterface;

/**
 * Apollo server communication class
 */
class Client
{
    use FakeClient;

    /**
     * Guzzle client
     * @var GuzzleClient
     */
    protected GuzzleClient $httpClient;

    /**
     * Authentication object
     * @var Auth
     */
    protected Auth $auth;

    /**
     * Apollo server URL
     * @var string
     */
    protected string $serverUrl;

    /**
     * Application ID
     * @var string
     */
    protected string $appId;

    /**
     * Cluster
     * @var string
     */
    protected string $cluster;

    /**
     * Secret key for authentication
     * @var string
     */
    protected string $secret = '';

    /**
     * Configuration version
     * @var array<string, string>
     */
    protected array $releaseKeys = [];

    /**
     * @var array<string, int>
     */
    protected array $notificationId = [];

    /**
     * Constructor
     *
     * @param array<string, mixed> $config Configuration parameters
     */
    public function __construct(array $config)
    {
        $this->serverUrl = $config['server_url'] ?? 'http://localhost:8080';
        $this->appId = $config['app_id'] ?? '';
        $this->cluster = $config['cluster'] ?? 'default';
        $this->secret = $config['secret'] ?? '';

        $this->httpClient = new GuzzleClient([
            'base_uri' => $this->serverUrl,
            'timeout' => 60,
            'connect_timeout' => 10,
            'headers' => $this->getHeaders(),
        ]);

        $this->auth = new Auth();
    }

    /**
     * Get configuration immediately
     *
     * @param string $namespace Namespace
     * @return array<string, mixed>
     * @throws ApolloException
     * @throws GuzzleException
     */
    public function getConfigImmediately(string $namespace): array
    {
        $uri = '/configs/' . $this->appId . '/' . $this->cluster . '/' . $namespace;
        $query = http_build_query([
            'releaseKey' => $this->releaseKeys[$namespace] ?? '',
            'ip' => $this->getClientIp(),
        ]);
        $uri .= '?' . $query;

        $body = $this->get($uri);

        $data = json_decode($body, true);

        if (!isset($data['configurations'])) {
            throw new ApolloException('Not found configurations key', 400, null, ['body' => $body]);
        }

        $this->releaseKeys[$namespace] = $data['releaseKey'];
        return $data['configurations'];
    }

    /**
     * @throws GuzzleException
     * @throws ApolloException
     */
    public function getConfig(string $namespace): string
    {
        $uri = '/configfiles/json/' . $this->appId . '/' . $this->cluster . '/' . $namespace;
        $query = http_build_query([
            'ip' => $this->getClientIp(),
        ]);
        $uri .= '?' . $query;

        return $this->get($uri);
    }

    /**
     * Listen for configuration changes
     *
     * @param array<string> $namespaces Namespace list
     * @param int $timeout Timeout in seconds
     * @return array<array<string, mixed>>
     * @throws ApolloException
     * @throws GuzzleException
     */
    public function listenConfig(array $namespaces, int $timeout = 60): ?array
    {
        $query = [
            'appId' => $this->appId,
            'cluster' => $this->cluster,
            'notifications' => json_encode(array_map(function (string $namespace) {
                return [
                    'namespaceName' => $namespace,
                    'notificationId' => $this->getNotificationId($namespace),
                ];
            }, $namespaces))
        ];
        $url = '/notifications/v2?' . http_build_query($query);
        $body = $this->get($url, [
            'timeout' => $timeout + 10,
        ]);

        return json_decode($body, true);
    }

    /**
     * Get client IP
     *
     * @return string
     */
    protected function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim($_SERVER[$key]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Get HTTP headers
     *
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * @param string $uri
     * @param array $options
     * @return string
     * @throws ApolloException
     * @throws GuzzleException
     */
    public function get(string $uri, array $options = []): string
    {
        $authHeaders = $this->auth->headers($uri, $this->appId, $this->secret);

        $options['headers'] = array_merge($this->getHeaders(), $options['headers'] ?? [], $authHeaders);

        $response = $this->httpGet($uri, $options);

        if ($response->getStatusCode() >= 400) {
            throw new ApolloException('Request failed with status code ' . $response->getStatusCode(), 400, null, ['response' => $response->getBody()]);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @throws GuzzleException
     */
    protected function httpGet(string $uri, array $options = []): ResponseInterface
    {
        $fakeResponse = $this->fakeGet($uri, $options);
        if ($fakeResponse) {
            return $fakeResponse;
        }

        return $this->httpClient->get($uri, $options);
    }

    /**
     * Get current release key
     *
     * @param string $namespace Namespace
     * @return string|null
     */
    public function getReleaseKey(string $namespace): ?string
    {
        return $this->releaseKeys[$namespace] ?? null;
    }

    /**
     * Set release key
     *
     * @param string $namespace Namespace
     * @param string $releaseKey Release key
     * @return void
     */
    public function setReleaseKey(string $namespace, string $releaseKey): void
    {
        $this->releaseKeys[$namespace] = $releaseKey;
    }

    public function getNotificationId(string $namespace): int
    {
        return $this->notificationId[$namespace] ?? -1;
    }


    public function setNotificationId(string $namespace, int $notificationId): void
    {
        $this->notificationId[$namespace] = $notificationId;
    }
}
