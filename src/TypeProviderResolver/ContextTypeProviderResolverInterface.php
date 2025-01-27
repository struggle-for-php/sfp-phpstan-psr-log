<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\TypeProviderResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use Sfp\PHPStan\Psr\Log\TypeProvider\ContextTypeProviderInterface;

interface ContextTypeProviderResolverInterface
{
    public function resolveContextTypeProvider(Scope $scope, Type $contextType): ContextTypeProviderInterface;
}
