<?php

namespace Leeqvip\Apollo\Parsers;

class Parser
{
    public static function create(string $namespace): ParserInterface
    {
        $ext = self::getExt($namespace);
        return match ($ext) {
            'yml', 'yaml' => new YamlParser(),
            default => new DefaultParser(),
        };
    }

    protected static function getExt(string $namespace): string
    {
        // 获取后缀
        $parts = explode('.', ltrim($namespace, '.'));

        // 如果只有一个部分，说明没有后缀
        if (count($parts) <= 1) {
            return '';
        }

        // 获取最后一个部分作为后缀
        $ext = end($parts);

        return strtolower($ext);
    }
}