<?php

declare(strict_types=1);

namespace Leeqvip\Apollo;

/**
 * Configuration management class
 */
class Config
{
    /**
     * Configuration data
     * @var array<string, array<string, mixed>>
     */
    protected array $configs = [];

    /**
     * Configuration change callbacks
     * @var array<string, array<callable>>
     */
    protected array $callbacks = [];

    /**
     * Get configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @param string $namespace Namespace
     * @return mixed
     */
    public function get(string $key, mixed $default = null, string $namespace = 'application'): mixed
    {
        if (!isset($this->configs[$namespace])) {
            return $default;
        }

        if (isset($this->configs[$namespace][$key])) {
            return $this->configs[$namespace][$key];
        }

        // Support dot-separated keys, such as database.host
        $keys = explode('.', $key);
        $value = $this->configs[$namespace];

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @param string $namespace Namespace
     * @return void
     */
    public function set(string $key, mixed $value, string $namespace = 'application'): void
    {
        if (!isset($this->configs[$namespace])) {
            $this->configs[$namespace] = [];
        }

        $this->configs[$namespace][$key] = $value;
    }

    /**
     * Batch set configurations
     * 
     * @param array<string, mixed> $configs Configuration array
     * @param string $namespace Namespace
     * @return void
     */
    public function setBatch(array $configs, string $namespace = 'application'): void
    {
        $this->configs[$namespace] = $configs;
        $this->triggerCallbacks($namespace);
    }

    /**
     * Get all configurations
     * 
     * @param string $namespace Namespace
     * @return array<string, mixed>
     */
    public function getAll(string $namespace = 'application'): array
    {
        return $this->configs[$namespace] ?? [];
    }

    /**
     * Register configuration change callback
     * 
     * @param callable $callback Callback function
     * @param string $namespace Namespace
     * @return void
     */
    public function registerCallback(callable $callback, string $namespace = '*'): void
    {
        if (!isset($this->callbacks[$namespace])) {
            $this->callbacks[$namespace] = [];
        }

        $this->callbacks[$namespace][] = $callback;
    }

    /**
     * Trigger configuration change callbacks
     * 
     * @param string $namespace Namespace
     * @return void
     */
    protected function triggerCallbacks(string $namespace): void
    {
        // Trigger callbacks for specific namespace
        if (isset($this->callbacks[$namespace])) {
            foreach ($this->callbacks[$namespace] as $callback) {
                call_user_func($callback, $this->configs[$namespace]);
            }
        }

        // Trigger global callbacks
        if (isset($this->callbacks['*'])) {
            foreach ($this->callbacks['*'] as $callback) {
                call_user_func($callback, $this->configs[$namespace], $namespace);
            }
        }
    }

    /**
     * Clear configurations
     * 
     * @param string|null $namespace Namespace
     * @return void
     */
    public function clear(?string $namespace = null): void
    {
        if ($namespace === null) {
            $this->configs = [];
        } else {
            unset($this->configs[$namespace]);
        }
    }
}
