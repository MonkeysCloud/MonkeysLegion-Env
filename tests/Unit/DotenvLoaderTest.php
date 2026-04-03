<?php

declare(strict_types=1);

namespace MonkeysLegion\Env\Tests\Unit;

use MonkeysLegion\Env\Loaders\DotenvLoader;
use MonkeysLegion\Env\Exceptions\EnvLoadingException;
use PHPUnit\Framework\TestCase;

class DotenvLoaderTest extends TestCase
{
    private DotenvLoader $loader;
    private string $testDir;

    protected function setUp(): void
    {
        $this->loader = new DotenvLoader();
        $this->testDir = sys_get_temp_dir() . '/env_test_' . uniqid();
        mkdir($this->testDir, 0777, true);
    }

    protected function tearDown(): void
    {
        // Clean up environment variables first
        putenv('APP_ENV');
        unset($_ENV['APP_ENV'], $_SERVER['APP_ENV']);
        putenv('TEST_VAR');
        unset($_ENV['TEST_VAR'], $_SERVER['TEST_VAR']);
        putenv('ANOTHER_VAR');
        unset($_ENV['ANOTHER_VAR'], $_SERVER['ANOTHER_VAR']);
        putenv('BASE_VAR');
        unset($_ENV['BASE_VAR'], $_SERVER['BASE_VAR']);
        putenv('LOCAL_VAR');
        unset($_ENV['LOCAL_VAR'], $_SERVER['LOCAL_VAR']);
        putenv('STR_VAR');
        unset($_ENV['STR_VAR'], $_SERVER['STR_VAR']);
        putenv('NUM_VAR');
        unset($_ENV['NUM_VAR'], $_SERVER['NUM_VAR']);
        putenv('BOOL_VAR');
        unset($_ENV['BOOL_VAR'], $_SERVER['BOOL_VAR']);

        // Clean up test directory and all files (including hidden)
        if (is_dir($this->testDir)) {
            $this->removeDirectory($this->testDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function testLoadReturnsEmptyArrayWhenNoEnvFilesExist(): void
    {
        $result = $this->loader->load($this->testDir);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testLoadReadsBasicEnvFile(): void
    {
        file_put_contents($this->testDir . '/.env', "TEST_VAR=test_value\nANOTHER_VAR=another_value");

        $result = $this->loader->load($this->testDir);

        $this->assertArrayHasKey('TEST_VAR', $result);
        $this->assertSame('test_value', $result['TEST_VAR']);
        $this->assertArrayHasKey('ANOTHER_VAR', $result);
        $this->assertSame('another_value', $result['ANOTHER_VAR']);
    }

    public function testLoadPrioritizesEnvironmentSpecificFiles(): void
    {
        // Create multiple .env files with different values
        file_put_contents($this->testDir . '/.env', "TEST_VAR=base");
        file_put_contents($this->testDir . '/.env.local', "TEST_VAR=local");
        file_put_contents($this->testDir . '/.env.production', "TEST_VAR=production");
        file_put_contents($this->testDir . '/.env.production.local', "TEST_VAR=production_local");

        // Set APP_ENV to production
        putenv('APP_ENV=production');

        $result = $this->loader->load($this->testDir);

        // .env.production.local should have highest priority
        $this->assertSame('production_local', $result['TEST_VAR']);
    }

    public function testLoadUsesDevEnvironmentByDefault(): void
    {
        file_put_contents($this->testDir . '/.env', "TEST_VAR=base");
        file_put_contents($this->testDir . '/.env.dev', "TEST_VAR=dev");

        // Don't set APP_ENV - should default to 'dev'
        putenv('APP_ENV');

        $result = $this->loader->load($this->testDir);

        $this->assertSame('dev', $result['TEST_VAR']);
    }

    public function testLoadProtectsSystemAppEnvVariable(): void
    {
        // Set system-level APP_ENV
        putenv('APP_ENV=production');
        $_ENV['APP_ENV'] = 'production';
        $_SERVER['APP_ENV'] = 'production';

        // Create .env file that tries to override it
        file_put_contents($this->testDir . '/.env', "APP_ENV=dev\nTEST_VAR=value");

        $result = $this->loader->load($this->testDir);

        // System APP_ENV should be protected
        $this->assertSame('production', $_ENV['APP_ENV']);
        $this->assertSame('production', $_SERVER['APP_ENV']);
        $this->assertSame('production', getenv('APP_ENV'));
    }

    public function testLoadThrowsExceptionWhenPathDoesNotExist(): void
    {
        $this->expectException(EnvLoadingException::class);
        $this->expectExceptionMessage('Environment path does not exist or is not a directory');

        $this->loader->load('/nonexistent/path');
    }

    public function testLoadThrowsExceptionWhenPathIsNotDirectory(): void
    {
        $file = $this->testDir . '/not_a_dir';
        touch($file);

        $this->expectException(EnvLoadingException::class);
        $this->expectExceptionMessage('Environment path does not exist or is not a directory');

        $this->loader->load($file);

        unlink($file);
    }

    public function testLoadFiltersOutNullValues(): void
    {
        // Create .env with empty values
        file_put_contents($this->testDir . '/.env', "TEST_VAR=value\nEMPTY_VAR=\nNULL_VAR=");

        $result = $this->loader->load($this->testDir);

        // Should only return non-null values
        $this->assertArrayHasKey('TEST_VAR', $result);
        // Empty strings should be filtered (they become null in some dotenv versions)
        foreach ($result as $value) {
            $this->assertNotNull($value, 'All values should be non-null');
        }
    }

    public function testLoadHandlesMultipleEnvironmentFiles(): void
    {
        // Create files with different variables
        file_put_contents($this->testDir . '/.env', "BASE_VAR=base");
        file_put_contents($this->testDir . '/.env.local', "LOCAL_VAR=local");

        putenv('APP_ENV');

        $result = $this->loader->load($this->testDir);

        // Should have variables from loaded .env files
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testLoadReturnsStringValuesOnly(): void
    {
        file_put_contents($this->testDir . '/.env', "STR_VAR=string\nNUM_VAR=123\nBOOL_VAR=true");

        $result = $this->loader->load($this->testDir);

        // All values should be strings (dotenv returns strings)
        foreach ($result as $key => $value) {
            $this->assertIsString($value, "Value for $key should be string");
        }
    }

    public function testLoadSetsEnvironmentVariablesInPhp(): void
    {
        file_put_contents($this->testDir . '/.env', "TEST_VAR=test_value");

        $result = $this->loader->load($this->testDir);

        // The loader returns the loaded data
        $this->assertArrayHasKey('TEST_VAR', $result);
        $this->assertSame('test_value', $result['TEST_VAR']);
    }
}
