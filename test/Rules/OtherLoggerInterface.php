<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

interface OtherLoggerInterface
{
    public function critical(string $message): void;
}
