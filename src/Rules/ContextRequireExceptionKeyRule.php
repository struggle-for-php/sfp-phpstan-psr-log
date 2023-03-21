<?php

namespace Sfp\PHPStan\Psr\Log\Rules;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PhpParser\Node;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
class ContextRequireExceptionKeyRule implements Rule
{
    private const LOGGER_LEVEL_METHODS = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug'
    ];

    private const ERROR_MISSED_EXCEPTION_KEY = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s() requires \'exception\' key. Current scope has Throwable variable - %s';

    public function getNodeType() : string
    {
        return Node\Expr\MethodCall::class;
    }

    /**
     * @param Node\Expr\MethodCall $node
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope) : array
    {
        if (!$node->name instanceof Node\Identifier) {
            return [];
        }

        $calledOnType = $scope->getType($node->var);
        if (!(new ObjectType('Psr\Log\LoggerInterface'))->isSuperTypeOf($calledOnType)->yes()) {
            return [];
        }

        $args = $node->getArgs();
        if (count($args) === 0) {
            return [];
        }

        $methodName = $node->name->toLowerString();

        $contextArgumentNo = 1;
        if ($methodName === 'log') {
            if (count($args) === 1) {
                return [];
            }
            $contextArgumentNo = 2;
        } elseif (!in_array($methodName, self::LOGGER_LEVEL_METHODS)) {
            return [];
        }

        $throwable = $this->findCurrentScopeThrowableVariable($scope);

        if ($throwable === null) {
            return [];
        }

        if (! isset($args[$contextArgumentNo])) {
            return [sprintf(self::ERROR_MISSED_EXCEPTION_KEY, $methodName, "\${$throwable}")];
        }

        $context = $args[$contextArgumentNo];

        if ($context instanceof Node\VariadicPlaceholder) {
            return [];
        }

        if ($context instanceof Node\Arg && self::contextDoesNotHavExceptionKey($context)) {
            return [sprintf(self::ERROR_MISSED_EXCEPTION_KEY, $methodName, "\${$throwable}")];
        }

        return [];
    }

    private function findCurrentScopeThrowableVariable(Scope $scope) : ?string
    {
        foreach ($scope->getDefinedVariables() as $var) {
            if ((new ObjectType(\Throwable::class))->isSuperTypeOf($scope->getVariableType($var))->yes()) {
                return $var;
            }
        }

        return null;
    }

    private static function contextDoesNotHavExceptionKey(Node\Arg $context) : bool
    {
        if (! $context->value instanceof Node\Expr\Array_) {
            return true;
        }

        if (count($context->value->items) === 0 ) {
            return true;
        }

        foreach ($context->value->items as $item) {
            assert($item->key instanceof Node\Scalar\String_);
            if ($item->key->value === 'exception') {
                return false;
            }
        }

        return true;
    }
}