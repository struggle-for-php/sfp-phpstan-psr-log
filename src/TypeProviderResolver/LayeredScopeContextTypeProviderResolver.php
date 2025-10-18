<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\TypeProviderResolver;

use LogicException;
use Override;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Sfp\PHPStan\Psr\Log\TypeProvider\ContextTypeProviderInterface;
use Sfp\PHPStan\Psr\Log\TypeProvider\Psr3ContextTypeProvider;

final class LayeredScopeContextTypeProviderResolver implements ContextTypeProviderResolverInterface
{
    /** @phpstan-var array<class-string, ContextTypeProviderInterface> */
    private $layerSet;

    /** @var AnyScopeContextTypeProviderResolver */
    private $anyScopeContextTypeProviderResolver;

    /** @var bool */
    private $fallbackAnyScope;

    /**
     * @phpstan-param array<class-string, ContextTypeProviderInterface> $layerSet
     */
    public function __construct(array $layerSet, bool $fallbackAnyScope = true)
    {
        $this->layerSet                            = $layerSet;
        $this->fallbackAnyScope                    = $fallbackAnyScope;
        $this->anyScopeContextTypeProviderResolver = new AnyScopeContextTypeProviderResolver(new Psr3ContextTypeProvider());
    }

    #[Override]
    public function resolveContextTypeProvider(Scope $scope): ContextTypeProviderInterface
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            if ($this->fallbackAnyScope) {
                return $this->anyScopeContextTypeProviderResolver->resolveContextTypeProvider($scope);
            }
            throw new LogicException('can not find belongs to ');
        }

        foreach ($this->layerSet as $interface => $contextTypeProvider) {
            if ($classReflection->implementsInterface($interface)) {
                return $contextTypeProvider;
            }
        }

        if (! $this->fallbackAnyScope) {
            throw new LogicException('can not find belongs to ');
        }

        return $this->anyScopeContextTypeProviderResolver->resolveContextTypeProvider($scope);
    }
}
