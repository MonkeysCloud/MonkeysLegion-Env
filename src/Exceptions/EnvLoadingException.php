<?php

declare(strict_types=1);

namespace MonkeysLegion\Env\Exceptions;

use RuntimeException;

/**
 * Exception thrown when environment variables cannot be loaded.
 */
class EnvLoadingException extends RuntimeException {}
