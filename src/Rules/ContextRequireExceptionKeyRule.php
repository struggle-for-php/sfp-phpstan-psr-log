<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use Throwable;

use function assert;
use function count;
use function in_array;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class ContextRequireExceptionKeyRule implements Rule
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

    /** @var bool */
    private $treatPhpDocTypesAsCertain;

    /** @var bool */
    private $reportMaybes;

    /** @var string */
    private $reportContextExceptionLogLevel;

    public function __construct(
        bool $treatPhpDocTypesAsCertain,
        bool $reportMaybes,
        string $reportContextExceptionLogLevel = 'debug'
    ) {
        $this->treatPhpDocTypesAsCertain      = $treatPhpDocTypesAsCertain;
        $this->reportMaybes                   = $reportMaybes;
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

        $logLevels         = [$methodName];
        $contextArgumentNo = 1;
        if ($methodName === 'log') {
            if (count($args) < 2) {
                return [];
            }

            $logLevelType = $scope->getType($args[0]->value);

            $logLevels = [];
            foreach ($logLevelType->getConstantStrings() as $constantString) {
                $logLevels[] = $constantString->getValue();
            }

            if (count($logLevels) === 0) {
                // cant find logLevels
                return [];
            }

            $contextArgumentNo = 2;
        } elseif (! in_array($methodName, LogLevelListInterface::LOGGER_LEVEL_METHODS)) {
            return [];
        }

        $throwable = $this->findCurrentScopeThrowableVariable($scope);

        if ($throwable === null) {
            return [];
        }

        if (! isset($args[$contextArgumentNo])) {
            if (! $this->hasReportLogLevel($logLevels)) {
                return [];
            }

            return [sprintf(self::ERROR_MISSED_EXCEPTION_KEY, $methodName, "\${$throwable}")];
        }

        $context = $args[$contextArgumentNo];
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        assert($context instanceof Node\Arg);

        if ($this->contextDoesNotHaveExceptionKey($context, $scope)) {
            if (! $this->hasReportLogLevel($logLevels)) {
                return [];
            }

            return [
                RuleErrorBuilder::message(
                    sprintf(self::ERROR_MISSED_EXCEPTION_KEY, $methodName, "\${$throwable}")
                )->identifier('sfp-psr-log.contextRequireExceptionKey')->build(),
            ];
        }

        return [];
    }

    /**
     * @phpstan-param list<string> $logLevels
     */
    public function hasReportLogLevel(array $logLevels): bool
    {
        foreach ($logLevels as $logLevel) {
            if ($this->isReportLogLevel($logLevel)) {
                return true;
            }
        }

        return false;
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

    private function contextDoesNotHaveExceptionKey(Node\Arg $context, Scope $scope): ?bool
    {
        if ($this->treatPhpDocTypesAsCertain) {
            $type = $scope->getType($context->value);
        } else {
            $type = $scope->getNativeType($context->value);
        }

        if ($type instanceof ErrorType) {
            return null;
        }

        $exceptionOffset = $type->hasOffsetValueType(new ConstantStringType('exception'));

        if (! $this->reportMaybes) {
            return $exceptionOffset->no();
        }

        if ($exceptionOffset->yes()) {
            return false;
        }

        if ($exceptionOffset->maybe()) {
            // check unions
            foreach ($type->getConstantArrays() as $constantArray) {
                if ($constantArray->hasOffsetValueType(new ConstantStringType('exception'))->no()) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
