<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
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

        $context = $args[$contextArgumentNo];

        if (! $context instanceof Node\Arg) {
            return [];
        }

        return self::keysAreNonEmptyString($context, $methodName);
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
     * @phpstan-return list<\PHPStan\Rules\RuleError>
     */
    private static function keysAreNonEmptyString(Node\Arg $context, string $methodName): array
    {
        if (! $context->value instanceof Node\Expr\Array_) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        if (count($context->value->items) === 0) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        $errors = [];
        foreach ($context->value->items as $item) {
            assert($item instanceof Node\Expr\ArrayItem);
            if ($item->key instanceof Node\Scalar\String_ && $item->key->value !== '') {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf(self::ERROR_UNEXPECTED_KEY, $methodName)
            )->identifier('sfp-psr-log.contextKeyNonEmptyString')->build();
        }

        return $errors;
    }
}
