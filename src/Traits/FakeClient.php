<?php

namespace Leeqvip\Apollo\Traits;

use Psr\Http\Message\ResponseInterface;

trait FakeClient
{
    protected static array $fakes = [];

    public static function fake(string $url, array $options, ResponseInterface $response): void
    {
        self::$fakes = [
            $url => $response,
        ];
    }

    public static function fakeGet(string $url, array $options = []): ?ResponseInterface
    {
        if (empty(self::$fakes)) {
            return null;
        }

        foreach (self::$fakes as $fakeUrl => $response) {
            if (!self::is(self::start($fakeUrl, '*'), $url)) {
                continue;
            }
            return $response;
        }

        return null;
    }

    protected static function start($value, $prefix): string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }

    protected static function is($pattern, $value, $ignoreCase = false): bool
    {
        $value = (string)$value;

        if (!is_iterable($pattern)) {
            $pattern = [$pattern];
        }

        foreach ($pattern as $pattern) {
            $pattern = (string)$pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern === '*' || $pattern === $value) {
                return true;
            }

            if ($ignoreCase && mb_strtolower($pattern) === mb_strtolower($value)) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#' . ($ignoreCase ? 'isu' : 'su'), $value) === 1) {
                return true;
            }
        }

        return false;
    }
}
