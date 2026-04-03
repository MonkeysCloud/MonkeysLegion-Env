<?php
declare(strict_types=1);

namespace MonkeysLegion\Env\Contracts;

interface EnvLoaderInterface
{
    /**
     * Load the environment variables from the given path.
     * 
     * @return array<string, string>
     */
    public function load(string $path): array;
}