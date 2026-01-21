# PHP Apollo Client

[![PHPUnit](https://github.com/leeqvip/php-apollo/actions/workflows/phpunit.yml/badge.svg)](https://github.com/leeqvip/php-apollo/actions/workflows/phpunit.yml)
[![Coverage Status](https://coveralls.io/repos/github/leeqvip/php-apollo/badge.svg)](https://coveralls.io/github/leeqvip/php-apollo)
[![Latest Stable Version](https://poser.pugx.org/leeqvip/php-apollo/v/stable)](https://packagist.org/packages/leeqvip/php-apollo)
[![Total Downloads](https://poser.pugx.org/leeqvip/php-apollo/downloads)](https://packagist.org/packages/leeqvip/php-apollo)
[![License](https://poser.pugx.org/leeqvip/php-apollo/license)](https://packagist.org/packages/leeqvip/php-apollo)


A comprehensive PHP client for Apollo configuration center, supporting configuration retrieval, real-time updates, local caching, and multiple configuration formats.

## Features

- ✅ PHP 8.0+ support with strict type declarations
- ✅ Singleton pattern design
- ✅ Multiple namespace support
- ✅ Real-time configuration updates (long polling)
- ✅ Local cache support with fallback mechanism
- ✅ Configuration change callbacks
- ✅ Dot-notation configuration key support
- ✅ Multiple configuration format support (YAML, JSON, Properties)
- ✅ Authentication mechanism (HMAC-SHA1 signature)
- ✅ Exception-based error handling
- ✅ GuzzleHTTP-based HTTP client
- ✅ PSR-4 autoloading standard

## Installation

Install via Composer:

```bash
composer require leeqvip/php-apollo
```

## Quick Start

### Basic Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Leeqvip\Apollo\Apollo;

// Initialize Apollo client
$apollo = Apollo::getInstance([
    'app_id' => 'YOUR_APP_ID',
    'server_url' => 'http://localhost:8080', // Apollo server address
    'cluster' => 'default', // Cluster name
    'namespaces' => ['application'], // Namespace list
    'cache_dir' => __DIR__ . '/cache', // Cache directory
    'secret' => 'YOUR_SECRET',
]);

$apollo->load();

// Get configuration
$value = $apollo->get('key', 'default');
echo "Configuration value: $value\n";

// Get all configurations
$configs = $apollo->getAll();
print_r($configs);
```

### Listening for Configuration Changes

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Leeqvip\Apollo\Apollo;

// Initialize Apollo client
$apollo = Apollo::getInstance([
    'app_id' => 'YOUR_APP_ID',
    'server_url' => 'http://localhost:8080',
]);

// Register configuration change callback
$apollo->onUpdate(function ($configs, $namespace) {
    echo "Configuration changed - Namespace: $namespace\n";
    print_r($configs);
});

// Start listening (blocking)
$apollo->listen();
```

### Multiple Namespaces

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Leeqvip\Apollo\Apollo;

// Initialize Apollo client with multiple namespaces
$apollo = Apollo::getInstance([
    'app_id' => 'YOUR_APP_ID',
    'server_url' => 'http://localhost:8080',
    'namespaces' => ['application', 'TEST1.application.yml', 'config.json'],
]);

// Get configurations from different namespaces
$appConfig = $apollo->get('key', 'default', 'application');
$testConfig = $apollo->get('key', 'default', 'TEST1.application.yml');
$jsonConfig = $apollo->get('key', 'default', 'config.json');
```

## Configuration Options

| Option | Type | Default Value | Description |
|--------|------|---------------|-------------|
| app_id | string | '' | Application ID |
| server_url | string | 'http://localhost:8080' | Apollo server address |
| cluster | string | 'default' | Cluster name |
| namespaces | array | ['application'] | Namespace list |
| cache_dir | string | sys_get_temp_dir() . '/apollo' | Cache directory |
| secret | string | '' | Authentication secret key |

## Supported Configuration Formats

The client automatically detects and parses different configuration formats based on the namespace suffix:

| Format | Suffix | Example Namespace |
|--------|--------|-------------------|
| Properties | .properties | application.properties |
| YAML | .yml, .yaml | application.yml, config.yaml |
| JSON | .json | config.json |
| Default (Properties) | No suffix | application |


## Troubleshooting

### Common Issues

1. **401 Unauthorized Error**
   - Check if your `secret` key is correct
   - Ensure the server time is synchronized (time skew error)
   - Verify that the application has permission to access the namespace

2. **404 Not Found Error**
   - Check if the Apollo server address is correct
   - Verify that the application ID exists in Apollo
   - Ensure the specified namespace exists

3. **Configuration Not Updating**
   - Check if the long polling is working correctly
   - Verify that the cache directory is writable
   - Ensure the namespace suffix matches the expected format

## Dependencies

- PHP >= 8.0
- guzzlehttp/guzzle >= 7.6
- (Optional) symfony/yaml >= 5.0 (for YAML parsing)

## License

MIT License