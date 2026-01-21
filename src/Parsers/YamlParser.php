<?php

namespace Leeqvip\Apollo\Parsers;

/**
 * YAML parser
 */
class YamlParser implements ParserInterface
{
    /**
     * Parse YAML content
     * 
     * @param string $content YAML content
     * @return array<string, mixed>
     */
    public function parse(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        // Use Symfony Yaml component if available
        if (class_exists('\Symfony\Component\Yaml\Yaml')) {
            return \Symfony\Component\Yaml\Yaml::parse($content);
        }

        // Fallback to using yaml_parse if available
        if (function_exists('yaml_parse')) {
            $result = yaml_parse($content);
            return is_array($result) ? $result : [];
        }

        // If no YAML parser is available, return empty array
        return [];
    }
}
