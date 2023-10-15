<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

/**
 * @internal Sfp\PHPStan\Psr\Log\Rules
 */
interface LogLevelListInterface
{
    public const LOGGER_LEVEL_METHODS = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];
}
