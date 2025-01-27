<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;

use function count;
use function in_array;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class LogMethodLevelRule implements Rule
{
    private const ERROR_INVALID_LEVEL = <<<'MESSAGE'
Parameter #1 $level of method Psr\Log\LoggerInterface::log() expects 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning', %s given.
MESSAGE;

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

        if ($methodName !== 'log') {
            return [];
        }

        $logLevelType = $scope->getType($args[0]->value);

        $logLevels = [];
        foreach ($logLevelType->getConstantStrings() as $constantString) {
            $logLevels[] = $constantString->getValue();
        }

        if (count($logLevels) === 0) {
            return [
                RuleErrorBuilder::message(
                    sprintf(self::ERROR_INVALID_LEVEL, $logLevelType->toPhpDocNode()->__toString())
                )->identifier('sfpPsrLog.logMethodLevel')->build(),
            ];
        }

        $invalidLogLevels = [];
        foreach ($logLevels as $logLevel) {
            if (! in_array($logLevel, LogLevelListInterface::LOGGER_LEVEL_METHODS, true)) {
                $invalidLogLevels[] = $logLevel;
            }
        }

        if (count($invalidLogLevels) === 0) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(self::ERROR_INVALID_LEVEL, $logLevelType->toPhpDocNode()->__toString())
            )->identifier('sfpPsrLog.logMethodLevel')->build(),
        ];
    }
}
