<?php

declare(strict_types=1);

namespace MonkeysLegion\Env;

use MonkeysLegion\Env\Contracts\EnvBootstrapperInterface;
use MonkeysLegion\Env\Contracts\EnvLoaderInterface;
use MonkeysLegion\Env\Contracts\EnvRepositoryInterface;

class EnvManager implements EnvBootstrapperInterface
{
    /**
     * Indicates if the environment variables have been bootstrapped.
     */
    private bool $booted = false;

    public function __construct(
        protected EnvLoaderInterface $loader,
        protected EnvRepositoryInterface $repository,
    ) {}

    /**
     * Bootstrap the given path.
     * 
     * @return void
     */
    public function boot(string $path): void
    {
        if ($this->booted) return;
        $data = $this->loader->load($path);
        $this->repository->setMany($data);
        $this->booted = true;
    }

    /**
     * Get the environment repository instance.
     * 
     * @return EnvRepositoryInterface
     */
    public function getRepository(): EnvRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Check if the environment variables have been bootstrapped.
     * 
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    // ========================================
    // Proxy methods for convenience
    // ========================================

    /**
     * Get the environment variable value for the given key.
     * Proxies to the repository for convenient access.
     * 
     * @param string $key The environment variable key
     * @param string|null $default The default value if not set
     * 
     * @return string|null
     */
    public function get(string $key, ?string $default = ''): ?string
    {
        return $this->repository->get($key, $default);
    }

    /**
     * Get an environment variable as a boolean.
     * Proxies to the repository for convenient access.
     * 
     * @param string $key The environment variable key
     * @param bool $default The default value if not set
     * 
     * @return bool
     */
    public function getBool(string $key, bool $default = false): bool
    {
        return $this->repository->getBool($key, $default);
    }

    /**
     * Get an environment variable as an integer.
     * Proxies to the repository for convenient access.
     * 
     * @param string $key The environment variable key
     * @param int $default The default value if not set
     * 
     * @return int
     */
    public function getInt(string $key, int $default = 0): int
    {
        return $this->repository->getInt($key, $default);
    }

    /**
     * Get an environment variable as a float.
     * Proxies to the repository for convenient access.
     * 
     * @param string $key The environment variable key
     * @param float $default The default value if not set
     * 
     * @return float
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        return $this->repository->getFloat($key, $default);
    }

    /**
     * Set the environment variable value for the given key.
     * Proxies to the repository for convenient access.
     * 
     * @param string $key The environment variable key
     * @param string $value The value to set
     * 
     * @return void
     */
    public function set(string $key, string $value): void
    {
        $this->repository->set($key, $value);
    }

    /**
     * Set multiple environment variable values at once.
     * Proxies to the repository for convenient access.
     * 
     * @param array<string, string> $variables An associative array of environment variables to set
     * 
     * @return void
     */
    public function setMany(array $variables): void
    {
        $this->repository->setMany($variables);
    }

    /**
     * Check if the environment variable exists for the given key.
     * Proxies to the repository for convenient access.
     * 
     * @param string $key The environment variable key
     * 
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->repository->has($key);
    }

    /**
     * Unset (remove) the environment variable for the given key.
     * Proxies to the repository for convenient access.
     * 
     * @param string $key The environment variable key to remove
     * 
     * @return void
     */
    public function unset(string $key): void
    {
        $this->repository->unset($key);
    }
}
