<?php
declare(strict_types=1);

namespace MonkeysLegion\Env\Repositories;

use MonkeysLegion\Env\Contracts\EnvRepositoryInterface;
use MonkeysLegion\Env\Exceptions\InvalidEnvironmentVariableException;

class NativeEnvRepository implements EnvRepositoryInterface
{
    /**
     * Resolve the environment variable from available sources.
     * Returns the value as a string, or false if not found.
     */
    private function resolveEnv(string $key): string|false
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        // getenv() returns false if not found, normalize to false
        if ($value === false) {
            return false;
        }
        
        return (string) $value;
    }

    /**
     * Get the environment variable value for the given key.
     * 
     * The method retrieves the value of an environment variable based on the provided key.
     * If the environment variable is not set, it returns the specified default value.
     * 
     * @param string $key The environment variable key.
     * @param string|null $default The default value to return if the environment variable is not set
     * 
     * @return string|null The environment variable value or the default value
     */
    public function get(string $key, ?string $default = ''): ?string
    {
        $value = $this->resolveEnv($key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }

    /**
     * Get an environment variable as a boolean.
     * 
     * Converts common string representations to boolean:
     * - true, (true), 1, yes, on => true
     * - false, (false), 0, no, off, empty string => false
     * 
     * @param string $key The environment variable key
     * @param bool $default The default value if not set
     * 
     * @return bool
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->resolveEnv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true', '(true)', '1', 'yes', 'on' => true,
            'false', '(false)', '0', 'no', 'off', '' => false,
            default => (bool) $value,
        };
    }

    /**
     * Get an environment variable as an integer.
     * 
     * @param string $key The environment variable key
     * @param int $default The default value if not set
     * 
     * @return int
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->resolveEnv($key);

        if ($value === false) {
            return $default;
        }

        return (int) $value;
    }

    /**
     * Get an environment variable as a float.
     * 
     * @param string $key The environment variable key
     * @param float $default The default value if not set
     * 
     * @return float
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->resolveEnv($key);

        if ($value === false) {
            return $default;
        }

        return (float) $value;
    }

    /**
     * @inheritDoc
     */    
    public function set(string $key, string $value): void
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function setMany(array $variables): void
    {
        foreach ($variables as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidEnvironmentVariableException(
                    'Environment variable keys must be strings, ' . gettype($key) . ' given'
                );
            }
            
            if (!is_string($value)) {
                throw new InvalidEnvironmentVariableException(
                    "Environment variable '$key' must be a string, " . gettype($value) . ' given'
                );
            }
            
            $this->set($key, $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->resolveEnv($key) !== false;
    }

    /**
     * @inheritDoc
     */
    public function unset(string $key): void
    {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
}