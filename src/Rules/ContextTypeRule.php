<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use Sfp\PHPStan\Psr\Log\TypeProvider\Psr3ContextTypeProvider;
use Sfp\PHPStan\Psr\Log\TypeProviderResolver\AnyScopeContextTypeProviderResolver;
use Sfp\PHPStan\Psr\Log\TypeProviderResolver\ContextTypeProviderResolverInterface;

use function count;
use function in_array;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class ContextTypeRule implements Rule
{
    /** @var ContextTypeProviderResolverInterface */
    private $contextTypeProviderResolver;

    public function __construct(?ContextTypeProviderResolverInterface $contextTypeProviderResolver)
    {
        $this->contextTypeProviderResolver = $contextTypeProviderResolver ?? new AnyScopeContextTypeProviderResolver(new Psr3ContextTypeProvider());
    }

    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Node\Identifier) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        $calledOnType = $scope->getType($node->var);
        if (! (new ObjectType('Psr\Log\LoggerInterface'))->isSuperTypeOf($calledOnType)->yes()) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        /** @var Node\Arg[] $args */
        $args = $node->getArgs();
        if (count($args) === 0) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        $methodName = $node->name->toLowerString();

        $contextArgumentNo = 1;
        if ($methodName === 'log') {
            $contextArgumentNo = 2;
        } elseif (! in_array($methodName, LogLevelListInterface::LOGGER_LEVEL_METHODS, true)) {
            return [];
        }

        if (! isset($args[$contextArgumentNo])) {
            return [];
        }

        $argContextType = $scope->getType($args[$contextArgumentNo]->value);

        $expectedContextType = $this->contextTypeProviderResolver->resolveContextTypeProvider($scope, $argContextType)->getType();

        $ret = $expectedContextType->accepts($argContextType, true);

        if ($ret->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Parameter #%d $context of method Psr\Log\LoggerInterface::%s() expects %s, %s given.',
                    $contextArgumentNo + 1,
                    $methodName,
                    (string) $expectedContextType->toPhpDocNode(),
                    (string) $argContextType->toPhpDocNode()
                )
            )->identifier('sfpPsrLog.contextType')->build(),
        ];
    }
}
