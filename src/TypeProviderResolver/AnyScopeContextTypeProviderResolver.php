<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\TypeProviderResolver;

use Override;
use PHPStan\Analyser\Scope;
use Sfp\PHPStan\Psr\Log\TypeProvider\ContextTypeProviderInterface;

final class AnyScopeContextTypeProviderResolver implements ContextTypeProviderResolverInterface
{
    /** @var ContextTypeProviderInterface */
    private $contextTypeProvider;
    public function __construct(ContextTypeProviderInterface $contextTypeProvider)
    {
        $this->contextTypeProvider = $contextTypeProvider;
    }

    #[Override]
    public function resolveContextTypeProvider(Scope $scope): ContextTypeProviderInterface
    {
        return $this->contextTypeProvider;
    }
}
