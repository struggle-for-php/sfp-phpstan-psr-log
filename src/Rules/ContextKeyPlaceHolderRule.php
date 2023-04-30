<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;

use function assert;
use function count;
use function implode;
use function in_array;
use function preg_match_all;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class ContextKeyPlaceHolderRule implements Rule
{
    private const ERROR_MISSED_CONTEXT = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s() is required, when placeholder braces exists - %s';
    private const ERROR_MISSED_KEY     = 'Parameter $message of logger method Psr\Log\LoggerInterface::%s() has placeholder braces, but context key is not found against them. - %s';

    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

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

        $message = $args[$contextArgumentNo - 1];
        if (! $message->value instanceof Node\Scalar\String_) {
            return [];
        }

        $matched = preg_match_all('#{([A-Za-z0-9_\.]+?)}#', $message->value->value, $matches);

        if ($matched === 0 || $matched === false) {
            return [];
        }

        if (! isset($args[$contextArgumentNo])) {
            return [sprintf(self::ERROR_MISSED_CONTEXT, $methodName, implode(',', $matches[0]))];
        }

        $context = $args[$contextArgumentNo];

        return self::contextDoesNotHavePlaceholderKey($context, $methodName, $matches[0], $matches[1]);
    }

    /**
     * @phpstan-param non-empty-array<string> $placeHolders
     */
    private static function contextDoesNotHavePlaceholderKey(Node\Arg $context, string $methodName, array $braces, array $placeHolders): array
    {
        $contextKeys = self::getContextKeys($context);
        foreach ($placeHolders as $i => $placeholder) {
            if (in_array($placeholder, $contextKeys, true)) {
                unset($braces[$i]);
            }
        }

        if (count($braces) === 0) {
            return [];
        }

        return [sprintf(self::ERROR_MISSED_KEY, $methodName, implode(',', $braces))];
    }

    private static function getContextKeys(Node\Arg $context): array
    {
        if (! $context->value instanceof Node\Expr\Array_) {
            return [];
        }

        if (count($context->value->items) === 0) {
            return [];
        }

        $keys = [];
        foreach ($context->value->items as $item) {
            assert($item instanceof Node\Expr\ArrayItem);

            if (isset($item->key->value)) {
                $keys[] = $item->key->value;
            }
        }

        return $keys;
    }
}
