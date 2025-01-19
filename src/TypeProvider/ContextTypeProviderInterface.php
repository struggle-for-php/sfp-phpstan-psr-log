<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\TypeProvider;

use PHPStan\Type\Type;

interface ContextTypeProviderInterface
{
    public function getType(): Type;
}
