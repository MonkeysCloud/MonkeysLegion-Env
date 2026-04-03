# MonkeysLegion-Env

A modern, type-safe environment variable management library for PHP 8.4+.

## Features

- 🔒 **Type-safe** - Full type hints with strict types enabled
- 🎯 **Simple API** - Clean, intuitive interface for environment management
- 📁 **Multiple .env files** - Support for environment-specific configurations
- 🔄 **Typed getters** - Built-in methods for boolean, integer, and float conversions
- 🛡️ **Error handling** - Custom exceptions for better debugging
- ✨ **Zero config** - Works out of the box with sensible defaults

## Installation

```bash
composer require monkeyscloud/monkeyslegion-env
```

## Quick Start

```php
use MonkeysLegion\Env\EnvManager;
use MonkeysLegion\Env\Loaders\DotenvLoader;
use MonkeysLegion\Env\Repositories\NativeEnvRepository;

// Initialize the manager
$manager = new EnvManager(
    new DotenvLoader(),
    new NativeEnvRepository()
);

// Load environment variables from .env files
$manager->boot(__DIR__);

// Now you can use the manager directly with proxy methods!
$appName = $manager->get('APP_NAME', 'MyApp');
$debug = $manager->getBool('DEBUG', false);
$port = $manager->getInt('PORT', 8080);
$timeout = $manager->getFloat('TIMEOUT', 30.0);

// You can also explicitly return null as default
$apiKey = $manager->get('API_KEY', null);
if ($apiKey === null) {
    // Handle missing API key
}

// Or access the repository if you prefer
$repo = $manager->getRepository();
$dbUrl = $repo->get('DATABASE_URL');
```

## Environment File Loading

The library loads `.env` files in the following priority order (highest to lowest):

1. `.env.{APP_ENV}.local` (e.g., `.env.production.local`)
2. `.env.{APP_ENV}` (e.g., `.env.production`)
3. `.env.local`
4. `.env`

The `APP_ENV` variable can be set as a system environment variable to determine which files to load. If not set, it defaults to `dev`.

### Example .env File

```env
APP_NAME=MyApplication
APP_ENV=production
DEBUG=false
PORT=8080
DATABASE_URL=mysql://user:pass@localhost/db
TIMEOUT=30.5
```

## API Reference

### EnvManager

The main orchestrator for loading and managing environment variables. Provides convenient proxy methods for direct access.

#### Methods

**Bootstrap:**

- `boot(string $path): void` - Load environment variables from the specified path
- `isBooted(): bool` - Check if environment has been bootstrapped

**Repository Access:**

- `getRepository(): EnvRepositoryInterface` - Get the repository instance

**Proxy Methods (Convenient Direct Access):**

- `get(string $key, ?string $default = ''): ?string` - Get a variable as a string (nullable)
- `getBool(string $key, bool $default = false): bool` - Get as boolean
- `getInt(string $key, int $default = 0): int` - Get as integer
- `getFloat(string $key, float $default = 0.0): float` - Get as float
- `set(string $key, string $value): void` - Set a single variable
- `setMany(array $variables): void` - Set multiple variables
- `has(string $key): bool` - Check if a variable exists

**Usage:**

```php
$manager->boot(__DIR__);

// Use proxy methods for convenience
$debug = $manager->getBool('DEBUG');
$port = $manager->getInt('PORT', 3000);

// Or access repository directly if needed
$repo = $manager->getRepository();
```

### NativeEnvRepository

Manages environment variables using PHP's native `$_ENV`, `$_SERVER`, and `getenv()`.

#### NativeEnvRepository Methods

**String Access:**

- `get(string $key, ?string $default = ''): ?string` - Get a variable as a string
  - Returns the environment variable value or the default
  - Can return `null` if explicitly set as default: `get('KEY', null)`
  - Empty string default if not provided: `get('KEY')`

**Typed Access:**

- `getBool(string $key, bool $default = false): bool` - Get as boolean
  - Recognizes: `true`, `false`, `1`, `0`, `yes`, `no`, `on`, `off`
- `getInt(string $key, int $default = 0): int` - Get as integer
- `getFloat(string $key, float $default = 0.0): float` - Get as float

**Modification:**

- `set(string $key, string $value): void` - Set a single variable
- `setMany(array $variables): void` - Set multiple variables at once

**Checking:**

- `has(string $key): bool` - Check if a variable exists

### DotenvLoader

Loads environment variables from `.env` files using [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).

#### DotenvLoader Methods

- `load(string $path): array` - Load environment files from the specified directory

## Error Handling

The library provides custom exceptions for better error handling:

### EnvLoadingException

Thrown when environment files cannot be loaded.

```php
use MonkeysLegion\Env\Exceptions\EnvLoadingException;

try {
    $manager->boot('/invalid/path');
} catch (EnvLoadingException $e) {
    // Handle loading errors
    echo "Failed to load environment: " . $e->getMessage();
}
```

### InvalidEnvironmentVariableException

Thrown when setting invalid environment variable types.

```php
use MonkeysLegion\Env\Exceptions\InvalidEnvironmentVariableException;

try {
    $repo->setMany(['KEY' => ['invalid' => 'array']]);
} catch (InvalidEnvironmentVariableException $e) {
    // Handle validation errors
    echo "Invalid variable: " . $e->getMessage();
}
```

## Advanced Usage

### Protecting System Variables

The library automatically protects system-level environment variables. If `APP_ENV` is set as a system variable, it won't be overwritten by `.env` files:

```bash
# System level
export APP_ENV=production

# Even if .env contains APP_ENV=dev, the system value (production) takes precedence
```

### Custom Implementations

You can create custom loaders and repositories by implementing the provided interfaces:

```php
use MonkeysLegion\Env\Contracts\EnvLoaderInterface;
use MonkeysLegion\Env\Contracts\EnvRepositoryInterface;

class CustomLoader implements EnvLoaderInterface {
    public function load(string $path): array {
        // Your custom loading logic
    }
}

class CustomRepository implements EnvRepositoryInterface {
    // Implement required methods
}

$manager = new EnvManager(new CustomLoader(), new CustomRepository());
```

## Type Conversion Examples

### Boolean Conversion

```php
// .env file:
// DEBUG=true
// FEATURE_FLAG=yes
// MAINTENANCE=0

$debug = $repo->getBool('DEBUG');           // true
$feature = $repo->getBool('FEATURE_FLAG');  // true
$maintenance = $repo->getBool('MAINTENANCE'); // false
```

### Integer and Float Conversion

```php
// .env file:
// PORT=8080
// MAX_CONNECTIONS=100
// TIMEOUT=30.5
// TAX_RATE=0.075

$port = $repo->getInt('PORT');              // 8080
$maxConn = $repo->getInt('MAX_CONNECTIONS'); // 100
$timeout = $repo->getFloat('TIMEOUT');      // 30.5
$taxRate = $repo->getFloat('TAX_RATE');     // 0.075
```

## Requirements

- PHP 8.4 or higher
- Composer

## Dependencies

- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) ^5.6

## Development

### Running Tests

```bash
composer test
```

### Code Analysis

```bash
composer analyze
```

### Test Coverage

```bash
composer test:coverage
```

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Author

MonkeysCloud

## Support

For issues, questions, or contributions, please use the GitHub issue tracker.
