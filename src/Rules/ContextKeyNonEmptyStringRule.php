<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;

use function assert;
use function count;
use function in_array;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class ContextKeyNonEmptyStringRule implements Rule
{
    // eg, DNumber
    private const ERROR_UNEXPECTED_KEY = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s(), key should be non empty string.';

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

        $contextArgumentNo = 1;
        if ($methodName === 'log') {
            if (
                count($args) < 2
                || ! $args[0]->value instanceof Node\Scalar\String_
            ) {
                return [];
            }

            $contextArgumentNo = 2;
        } elseif (! in_array($methodName, LogLevelListInterface::LOGGER_LEVEL_METHODS)) {
            return [];
        }

        $context = $args[$contextArgumentNo];

        if (! $context instanceof Node\Arg) {
            return [];
        }

        return self::keysAreNonEmptyString($context, $methodName);
    }

    /**
     * @phpstan-return list<string>
     */
    private static function keysAreNonEmptyString(Node\Arg $context, string $methodName): array
    {
        if (! $context->value instanceof Node\Expr\Array_) {
            return [];
        }

        if (count($context->value->items) === 0) {
            return [];
        }

        $indexes = [];
        foreach ($context->value->items as $item) {
            assert($item instanceof Node\Expr\ArrayItem);
            if ($item->key instanceof Node\Scalar\String_ && $item->key->value !== '') {
                continue;
            }

            $indexes[] = sprintf(self::ERROR_UNEXPECTED_KEY, $methodName);
        }

        return $indexes;
    }
}
