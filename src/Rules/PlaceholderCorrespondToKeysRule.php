<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

use function assert;
use function count;
use function implode;
use function in_array;
use function is_string;
use function preg_match_all;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class PlaceholderCorrespondToKeysRule implements Rule
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

        $contextArgumentNo = 1;
        if ($methodName === 'log') {
            if (
                count($args) < 2
                || ! $args[0]->value instanceof Node\Scalar\String_
            ) {
                // @codeCoverageIgnoreStart
                return []; // @codeCoverageIgnoreEnd
            }

            $contextArgumentNo = 2;
        } elseif (! in_array($methodName, LogLevelListInterface::LOGGER_LEVEL_METHODS)) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
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
            return [
                RuleErrorBuilder::message(
                    sprintf(self::ERROR_MISSED_CONTEXT, $methodName, implode(',', $matches[0]))
                )->identifier('sfp-psr-log.placeHolderCorrespondToKeysMissedContext')->build(),
            ];
        }

        $context = $args[$contextArgumentNo];

        return self::contextDoesNotHavePlaceholderKey($context, $methodName, $matches[0], $matches[1]);
    }

    /**
     * @phpstan-param list<string> $braces
     * @phpstan-param list<string> $placeHolders
     * phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
     * @phpstan-return list<\PHPStan\Rules\RuleError>
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

        return [
            RuleErrorBuilder::message(
                sprintf(self::ERROR_MISSED_KEY, $methodName, implode(',', $braces))
            )->identifier('sfp-psr-log.placeHolderCorrespondToKeysMissedKey')->build(),
        ];
    }

    /**
     * @phpstan-return list<string>
     */
    private static function getContextKeys(Node\Arg $context): array
    {
        if (! $context->value instanceof Node\Expr\Array_) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        if (count($context->value->items) === 0) {
            return [];
        }

        $keys = [];
        foreach ($context->value->items as $item) {
            assert($item instanceof Node\Expr\ArrayItem);
            if (isset($item->key->value) && is_string($item->key->value)) {
                $keys[] = $item->key->value;
            }
        }

        return $keys;
    }
}
