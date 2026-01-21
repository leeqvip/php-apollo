<?php

namespace Leeqvip\Apollo\Parsers;

/**
 */
class DefaultParser  implements ParserInterface
{
    /**
     * Parse content
     * 
     * @param string $content Properties content
     * @return array<string, mixed>
     */
    public function parse(string $content): array
    {
        return json_decode($content, true);
    }

}