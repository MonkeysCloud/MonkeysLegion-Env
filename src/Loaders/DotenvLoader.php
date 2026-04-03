<?php

declare(strict_types=1);

namespace MonkeysLegion\Env\Loaders;

use Dotenv\Dotenv;
use MonkeysLegion\Env\Contracts\EnvLoaderInterface;
use MonkeysLegion\Env\Exceptions\EnvLoadingException;
use Exception;

class DotenvLoader implements EnvLoaderInterface
{
    /**
     * The default environment name when APP_ENV is not set.
     */
    private const DEFAULT_ENV = 'dev';

    /**
     * The environment variable name that determines which environment to load.
     */
    private const ENV_VAR_NAME = 'APP_ENV';

    /**
     * @inheritDoc
     * 
     * @throws EnvLoadingException If the path does not exist or environment files cannot be loaded
     */
    public function load(string $path): array
    {
        if (!is_dir($path)) {
            throw new EnvLoadingException("Environment path does not exist or is not a directory: $path");
        }

        try {
            // 1. Capture the system value FIRST
            $systemEnv = getenv(self::ENV_VAR_NAME);
            $appEnv = $systemEnv ?: self::DEFAULT_ENV;

            // 2. Build files array in priority order (highest to lowest)
            $files = array_filter([
                ".env.$appEnv.local",
                ".env.$appEnv",
                ".env.local",
                ".env"
            ]);

            $dotenv = Dotenv::createImmutable($path, $files);
            $data = $dotenv->safeLoad();

            // Filter out null values to ensure array<string, string>
            $data = array_filter($data, fn($value) => $value !== null);

            // 3. MANDATORY: Re-apply the system variable if it was exported
            // This prevents the .env file from "downgrading" your production export
            if ($systemEnv) {
                $_ENV[self::ENV_VAR_NAME] = $systemEnv;
                $_SERVER[self::ENV_VAR_NAME] = $systemEnv;
            }

            return $data;
        } catch (Exception $e) {
            throw new EnvLoadingException(
                "Failed to load environment variables from path: $path. Error: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
