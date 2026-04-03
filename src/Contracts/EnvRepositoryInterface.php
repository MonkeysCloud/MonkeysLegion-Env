<?php

declare(strict_types=1);

namespace MonkeysLegion\Env\Contracts;

interface EnvRepositoryInterface
{
    /**
     * Get the environment variable value for the given key.
     * 
     * The method retrieves the value of an environment variable based on the provided key.
     * If the environment variable is not set, it returns the specified default value.
     * 
     * @param string $key The environment variable key.
     * @param string $default The default value to return if the environment variable is not set
     * 
     * @return string The environment variable value or the default value
     */
    public function get(string $key, string $default = ''): string;

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
    public function getBool(string $key, bool $default = false): bool;

    /**
     * Get an environment variable as an integer.
     * 
     * @param string $key The environment variable key
     * @param int $default The default value if not set
     * 
     * @return int
     */
    public function getInt(string $key, int $default = 0): int;

    /**
     * Get an environment variable as a float.
     * 
     * @param string $key The environment variable key
     * @param float $default The default value if not set
     * 
     * @return float
     */
    public function getFloat(string $key, float $default = 0.0): float;

    /**
     * Set the environment variable value for the given key.
     * 
     * @return void
     */
    public function set(string $key, string $value): void;

    /**
     * Set multiple environment variable values at once.
     * 
     * @param array<string, string> $variables An associative array of environment variables to set
     * where the key is the variable name and the value is the variable value.
     * 
     * @return void
     */
    public function setMany(array $variables): void;

    /**
     * Check if the environment variable exists for the given key.
     * 
     * @return bool
     */
    public function has(string $key): bool;
}
