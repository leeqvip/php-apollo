<?php

namespace Leeqvip\Apollo\Auths;

use Leeqvip\Apollo\Exceptions\ApolloException;

class Auth
{
    protected const DELIMITER = "\n";

    protected const AUTH_TYPE = 'Apollo';

    public function headers(string $uri, string $appId, string $secret,): array
    {
        $pathAndQuery = $this->getPathAndQuery($uri);
        $timestamp = $this->getTimestamp();
        $signature = $this->getSignature($timestamp . self::DELIMITER . $pathAndQuery, $secret);

        return [
            'Authorization' => $this->getAuthorization($appId, $signature),
            'Timestamp' => $timestamp,
        ];
    }

    protected function getAuthorization(string $appId, string $signature): string
    {
        // Apollo ${appId}:${signature}
        return self::AUTH_TYPE . ' ' . $appId . ':' . $signature;
    }

    protected function getSignature(string $raw, string $secret): string
    {
        return base64_encode(hash_hmac('sha1', $raw, $secret, true));
    }

    protected function getTimestamp(): int
    {
        // return 1768980661773;
        return (int) (microtime(true) * 1000);
    }

    protected function getPathAndQuery(string $uri): string
    {
        $parsed = parse_url($uri);
        if (!$parsed) {
            throw new ApolloException('Invalid URI: ' . $uri);
        }
        $pathAndQuery = $parsed['path'] ?? '';
        if ($query = $parsed['query'] ?? '') {
            $pathAndQuery .= '?' . $query;
        }
        return $pathAndQuery;
    }
}
