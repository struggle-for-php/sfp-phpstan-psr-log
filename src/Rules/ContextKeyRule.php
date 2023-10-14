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
use function preg_match;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class ContextKeyRule implements Rule
{
    // eg, DNumber
    private const ERROR_NOT_NON_EMPTY_STRING = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s(), key should be non empty string.';

    private const ERROR_NOT_MATCH_ORIGINAL_PATTERN = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s(), key should be match %s.';

    /** @var string|null */
    private $contextKeyOriginalPattern;

    public function __construct(?string $contextKeyOriginalPattern = null)
    {
        $this->contextKeyOriginalPattern = $contextKeyOriginalPattern;
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

        if (self::contextIsEmpty($context)) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        $errors = self::keysAreNonEmptyString($context->value, $methodName);

        if ($errors !== []) {
            return $errors;
        }

        return self::originalPatternMatches($context->value, $methodName);
    }

    /**
     * @phpstan-assert-if-false Node\Expr\Array_ $context->value
     */
    private static function contextIsEmpty(Node\Arg $context): bool
    {
        if (! $context->value instanceof Node\Expr\Array_) {
            // @codeCoverageIgnoreStart
            return true; // @codeCoverageIgnoreEnd
        }

        if (count($context->value->items) === 0) {
            // @codeCoverageIgnoreStart
            return true; // @codeCoverageIgnoreEnd
        }

        return false;
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
     * @phpstan-return list<\PHPStan\Rules\RuleError>
     */
    private static function keysAreNonEmptyString(Node\Expr\Array_ $contextArray, string $methodName): array
    {
        $errors = [];
        foreach ($contextArray->items as $item) {
            assert($item instanceof Node\Expr\ArrayItem);
            if ($item->key instanceof Node\Scalar\String_ && $item->key->value !== '') {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf(self::ERROR_NOT_NON_EMPTY_STRING, $methodName)
            )->identifier('sfp-psr-log.contextKeyNonEmptyString')->build();
        }

        return $errors;
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
     * @phpstan-return list<\PHPStan\Rules\RuleError>
     */
    private function originalPatternMatches(Node\Expr\Array_ $contextArray, string $methodName): array
    {
        if (! $this->contextKeyOriginalPattern) {
            return [];
        }

        $errors = [];
        foreach ($contextArray->items as $item) {
            assert($item instanceof Node\Expr\ArrayItem);
            if (! $item->key instanceof Node\Scalar\String_) {
                continue;
            }

            $matched = preg_match($this->contextKeyOriginalPattern, $item->key->value, $matches);

            if ($matched === false) {
                continue;
            }

            if ($matched === 0) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(self::ERROR_NOT_MATCH_ORIGINAL_PATTERN, $methodName, $this->contextKeyOriginalPattern)
                )->identifier('sfp-psr-log.contextKeyOriginalPattern')->build();
            }
        }

        return $errors;
    }
}
