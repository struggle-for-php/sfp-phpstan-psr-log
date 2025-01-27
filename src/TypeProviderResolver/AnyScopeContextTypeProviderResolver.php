<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\TypeProviderResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use Sfp\PHPStan\Psr\Log\TypeProvider\ContextTypeProviderInterface;

final class AnyScopeContextTypeProviderResolver implements ContextTypeProviderResolverInterface
{
    /** @var ContextTypeProviderInterface */
    private $contextTypeProvider;
    public function __construct(ContextTypeProviderInterface $contextTypeProvider)
    {
        $this->contextTypeProvider = $contextTypeProvider;
    }

    public function resolveContextTypeProvider(Scope $scope, Type $contextType): ContextTypeProviderInterface
    {
        return $this->contextTypeProvider;
    }
}
