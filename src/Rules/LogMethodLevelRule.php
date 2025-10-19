<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;

use function count;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class LogMethodLevelRule implements Rule
{
    private const ERROR_INVALID_LEVEL = <<<'MESSAGE'
Parameter #1 $level of method Psr\Log\LoggerInterface::log() expects %s, %s given.
MESSAGE;

    private RuleLevelHelper $ruleLevelHelper;

    private UnionType $acceptingLogLevel;

    public function __construct(RuleLevelHelper $ruleLevelHelper)
    {
        $this->ruleLevelHelper   = $ruleLevelHelper;
        $this->acceptingLogLevel = new UnionType([
            new ConstantStringType('emergency'),
            new ConstantStringType('alert'),
            new ConstantStringType('critical'),
            new ConstantStringType('error'),
            new ConstantStringType('warning'),
            new ConstantStringType('notice'),
            new ConstantStringType('info'),
            new ConstantStringType('debug'),
        ]);
    }

    #[Override]
    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    #[Override]
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

        $args = $node->getArgs();
        if (count($args) === 0) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        $methodName = $node->name->toLowerString();

        if ($methodName !== 'log') {
            return [];
        }

        $argLevel = $scope->getType($args[0]->value);

        $acceptsResult = $this->ruleLevelHelper->accepts($this->acceptingLogLevel, $argLevel, $scope->isDeclareStrictTypes());

        if ($acceptsResult->result === true) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    self::ERROR_INVALID_LEVEL,
                    $this->acceptingLogLevel->toPhpDocNode()->__toString(),
                    $argLevel->toPhpDocNode()->__toString()
                )
            )
                ->acceptsReasonsTip($acceptsResult->reasons)
                ->identifier('sfpPsrLog.logMethodLevel')
                ->build(),
        ];
    }
}
