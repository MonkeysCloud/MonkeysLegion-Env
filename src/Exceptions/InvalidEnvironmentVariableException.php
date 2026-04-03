<?php

declare(strict_types=1);

namespace MonkeysLegion\Env\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when an environment variable has an invalid type or value.
 */
class InvalidEnvironmentVariableException extends InvalidArgumentException {}
