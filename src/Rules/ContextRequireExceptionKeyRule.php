<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use Throwable;

use function assert;
use function count;
use function in_array;
use function is_string;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
class ContextRequireExceptionKeyRule implements Rule
{
    private const LOGGER_LEVELS = [
        'emergency' => 7,
        'alert'     => 6,
        'critical'  => 5,
        'error'     => 4,
        'warning'   => 3,
        'notice'    => 2,
        'info'      => 1,
        'debug'     => 0,
    ];

    private const ERROR_MISSED_EXCEPTION_KEY = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s() requires \'exception\' key. Current scope has Throwable variable - %s';

    /** @var string */
    private $reportContextExceptionLogLevel;

    public function __construct(string $reportContextExceptionLogLevel = 'debug')
    {
        $this->reportContextExceptionLogLevel = $reportContextExceptionLogLevel;
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
            return [];
        }

        $calledOnType = $scope->getType($node->var);
        if (! (new ObjectType('Psr\Log\LoggerInterface'))->isSuperTypeOf($calledOnType)->yes()) {
            return [];
        }

        $args = $node->getArgs();
        if (count($args) === 0) {
            return [];
        }

        $methodName = $node->name->toLowerString();

        $logLevel          = $methodName;
        $contextArgumentNo = 1;
        if ($methodName === 'log') {
            if (
                count($args) < 2
                || ! $args[0]->value instanceof Node\Scalar\String_
            ) {
                return [];
            }

            $logLevel          = $args[0]->value->value;
            $contextArgumentNo = 2;
        } elseif (! in_array($methodName, LogLevelListInterface::LOGGER_LEVEL_METHODS)) {
            return [];
        }

        $throwable = $this->findCurrentScopeThrowableVariable($scope);

        if ($throwable === null) {
            return [];
        }

        if (! isset($args[$contextArgumentNo])) {
            if (! $this->isReportLogLevel($logLevel)) {
                return [];
            }

            return [sprintf(self::ERROR_MISSED_EXCEPTION_KEY, $methodName, "\${$throwable}")];
        }

        $context = $args[$contextArgumentNo];
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        assert($context instanceof Node\Arg);

        if (self::contextDoesNotHaveExceptionKey($context, $scope)) {
            if (! $this->isReportLogLevel($logLevel)) {
                return [];
            }

            return [sprintf(self::ERROR_MISSED_EXCEPTION_KEY, $methodName, "\${$throwable}")];
        }

        return [];
    }

    public function isReportLogLevel(string $logLevel): bool
    {
        return self::LOGGER_LEVELS[$logLevel] >= self::LOGGER_LEVELS[$this->reportContextExceptionLogLevel];
    }

    private function findCurrentScopeThrowableVariable(Scope $scope): ?string
    {
        foreach ($scope->getDefinedVariables() as $var) {
            if ((new ObjectType(Throwable::class))->isSuperTypeOf($scope->getVariableType($var))->yes()) {
                return $var;
            }
        }

        return null;
    }

    private static function contextDoesNotHaveExceptionKey(Node\Arg $context, Scope $scope): bool
    {
        if (! $context->value instanceof Node\Expr\Array_) {
            if ($context->value instanceof Node\Expr\Variable) {
                assert(is_string($context->value->name));
                $contextVariable = $scope->getVariableType($context->value->name);
                if (
                    $contextVariable instanceof ArrayType
                    && $contextVariable->hasOffsetValueType(new ConstantStringType('exception'))->yes()
                ) {
                    return false;
                }
            }
            return true;
        }

        foreach ($context->value->items as $item) {
            assert($item instanceof Node\Expr\ArrayItem);
            if ($item->key instanceof Node\Scalar\String_ && $item->key->value === 'exception') {
                return false;
            }
        }

        return true;
    }
}
