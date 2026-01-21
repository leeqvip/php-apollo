<?php

namespace Leeqvip\Apollo\Parsers;

interface ParserInterface
{
    function parse(string $content): array;
}
