<?php

declare(strict_types=1);

namespace Leeqvip\Apollo;

use GuzzleHttp\Exception\GuzzleException;
use Leeqvip\Apollo\Exceptions\ApolloException;
use Leeqvip\Apollo\Parsers\Parser;

/**
 * Apollo client core class
 */
class Apollo
{
    /**
     * Instance
     * @var Apollo|null
     */
    protected static ?Apollo $instance = null;

    /**
     * Configuration management
     * @var Config
     */
    protected Config $config;

    /**
     * Apollo server communication client
     * @var Client
     */
    protected Client $client;

    /**
     * Configuration
     * @var array<string, mixed>
     */
    protected array $options;

    /**
     * Namespace list
     * @var array<string>
     */
    protected array $namespaces = ['application'];

    protected string $defaultNamespace = 'application';

    /**
     * Cache directory
     * @var string
     */
    protected string $cacheDir;

    /**
     * Constructor
     *
     * @param array<string, mixed> $options Configuration parameters
     * @throws ApolloException
     * @throws GuzzleException
     */
    protected function __construct(array $options)
    {
        $this->options = $options;
        $this->config = new Config();
        $this->client = new Client($options);
        $this->namespaces = $options['namespaces'] ?? $this->namespaces;
        $this->defaultNamespace = count($this->namespaces) > 0 ? $this->namespaces[0] : $this->defaultNamespace;
        $this->cacheDir = $options['cache_dir'] ?? sys_get_temp_dir() . '/apollo';

        // Initialize
        $this->init();
    }

    /**
     * Get instance (singleton pattern)
     *
     * @param array<string, mixed> $options Configuration parameters
     * @return Apollo
     * @throws ApolloException|GuzzleException
     */
    public static function getInstance(array $options = []): Apollo
    {
        if (!self::$instance) {
            self::$instance = new self($options);
        }
        return self::$instance;
    }

    /**
     * Initialize
     * @return void
     * @throws ApolloException|GuzzleException
     */
    private function init(): void
    {
        // Ensure cache directory exists
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        $this->loadOrPull();
    }

    /**
     * @throws GuzzleException
     * @throws ApolloException
     */
    private function loadOrPull(): void
    {
        // Load cache
        if ($this->loadCache()) {
            return;
        }

        $this->pullConfigs();
    }

    /**
     * @throws ApolloException
     * @throws GuzzleException
     */
    public function pull(): void
    {
        $this->pullConfigs();
    }

    /**
     * Pull configurations
     *
     * @return void
     * @throws ApolloException
     * @throws GuzzleException
     */
    protected function pullConfigs(): void
    {
        foreach ($this->namespaces as $namespace) {
            $configString = $this->client->getConfig($namespace);
            $config = $this->parse($configString, $namespace);
            $this->config->setBatch($config, $namespace);
            $this->saveCache($namespace, $config);
        }
    }

    protected function parse(string $content, string $namespace): array
    {
        $parser = Parser::create($namespace);
        return $parser->parse($content);
    }

    /**
     *
     * Get config instance
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get configuration
     *
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @param string|null $namespace Namespace
     * @return mixed
     */
    public function get(string $key, mixed $default = null, ?string $namespace = null): mixed
    {
        return $this->config->get($key, $default, empty($namespace) ? $this->defaultNamespace : $namespace);
    }

    /**
     * Get all configurations
     *
     * @param string|null $namespace Namespace
     * @return array<string, mixed>
     */
    public function all(?string $namespace = null): array
    {
        return $this->config->all(empty($namespace) ? $this->defaultNamespace : $namespace);
    }

    /**
     * Register configuration change callback
     *
     * @param callable $callback Callback function
     * @param string $namespace Namespace
     * @return void
     */
    public function onUpdate(callable $callback, string $namespace = '*'): void
    {
        $this->config->registerCallback($callback, $namespace);
    }

    /**
     * Start listening for configuration changes
     *
     * @param callable|null $callback Change callback
     * @return void
     */
    public function listen(?callable $callback = null): void
    {
        while (true) {
            $notifications = $this->client->listenConfig($this->namespaces);
            if (!empty($notifications)) {
                foreach ($notifications as $notification) {
                    ['namespaceName' => $namespace, 'notificationId' => $notificationId] = $notification;
                    $config = $this->client->getConfigImmediately($namespace);

                    $this->config->setBatch($config, $namespace);
                    $this->saveCache($namespace, $config);
                    // notificationId
                    $this->client->setNotificationId($namespace, $notificationId);

                    if ($callback) {
                        call_user_func($callback, $config, $namespace);
                    }
                }
            }
        }
    }

    /**
     * Load from local cache
     *
     * @return bool
     */
    protected function loadCache(): bool
    {
        foreach ($this->namespaces as $namespace) {
            $cacheFile = $this->getCacheFile($namespace);
            if (!file_exists($cacheFile)) {
                return false;
            }
            $config = require $cacheFile;
            if (!is_array($config)) {
                return false;
            }
            $this->config->setBatch($config, $namespace);
        }

        return true;
    }

    /**
     * Save cache
     *
     * @param string $namespace Namespace
     * @param array<string, mixed> $config Configuration
     * @return void
     */
    protected function saveCache(string $namespace, array $config): void
    {
        $cacheFile = $this->getCacheFile($namespace);
        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        file_put_contents($cacheFile, $content);
    }

    /**
     * @param string $namespace
     * @return void
     */
    public function removeCache(string $namespace = '*'): void
    {
        foreach ($this->namespaces as $namespace) {
            $cacheFile = $this->getCacheFile($namespace);
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }

    /**
     * Get cache file path
     *
     * @param string $namespace Namespace
     * @return string
     */
    protected function getCacheFile(string $namespace): string
    {
        return $this->cacheDir . '/' . md5($this->options['app_id'] . '_' . $namespace) . '.php';
    }

    /**
     * Disable cloning
     */
    public function __clone(): void
    {
    }

    /**
     * Disable serialization
     */
    public function __wakeup(): void
    {
    }
}
