<?php

declare(strict_types=1);

namespace MonkeysLegion\Env\Contracts;

interface EnvBootstrapperInterface
{
    /**
     * Bootstrap the given path.
     * 
     * @return void
     */
    public function boot(string $path): void;

    /**
     * Get the environment repository instance.
     * 
     * @return EnvRepositoryInterface
     */
    public function getRepository(): EnvRepositoryInterface;

    /**
     * Check if the environment variables have been bootstrapped.
     * 
     * @return bool
     */
    public function isBooted(): bool;
}
