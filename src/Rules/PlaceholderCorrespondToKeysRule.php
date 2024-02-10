<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

use function count;
use function implode;
use function in_array;
use function preg_match_all;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class PlaceholderCorrespondToKeysRule implements Rule
{
    private const ERROR_MISSED_CONTEXT = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s() is required, when placeholder braces exists - %s';

    private const ERROR_EMPTY_CONTEXT = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s() is empty, when placeholder braces exists';

    private const ERROR_MISSED_KEY = 'Parameter $message of logger method Psr\Log\LoggerInterface::%s() has placeholder braces, but context key is not found against them. - %s';

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
        } elseif (! in_array($methodName, LogLevelListInterface::LOGGER_LEVEL_METHODS, true)) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        $message = $args[$contextArgumentNo - 1];
        $strings = $scope->getType($message->value)->getConstantStrings();

        if (count($strings) === 0) {
            return [];
        }

        $errors = [];
        foreach ($strings as $constantStringType) {
            $message = $constantStringType->getValue();

            $matched = preg_match_all('#{([A-Za-z0-9_\.]+?)}#', $message, $matches);

            if ($matched === 0 || $matched === false) {
                continue;
            }

            if (! isset($args[$contextArgumentNo])) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(self::ERROR_MISSED_CONTEXT, $methodName, implode(',', $matches[0]))
                )->identifier('sfp-psr-log.placeholderCorrespondToKeysMissedContext')->build();

                continue;
            }

            $context = $args[$contextArgumentNo];

            $contextDoesNotHaveError = self::contextDoesNotHavePlaceholderKey($scope->getType($context->value), $methodName, $matches[0], $matches[1]);
            if ($contextDoesNotHaveError instanceof RuleError) {
                $errors[] = $contextDoesNotHaveError;
            }
        }

        return $errors;
    }

    /**
     * @phpstan-param list<string> $braces
     * @phpstan-param list<string> $placeholders
     */
    private static function contextDoesNotHavePlaceholderKey(Type $arrayType, string $methodName, array $braces, array $placeholders): ?RuleError
    {
        if ($arrayType->isIterableAtLeastOnce()->no()) {
            return RuleErrorBuilder::message(
                self::ERROR_EMPTY_CONTEXT
            )->identifier('sfp-psr-log.placeholderCorrespondToKeysMissedKey')->build();
        }

        $constantArrays = $arrayType->getConstantArrays();

        foreach ($constantArrays as $constantArray) {
            $contextKeys = [];
            $checkBraces = $braces;
            foreach ($constantArray->getKeyTypes() as $keyType) {
                if ($keyType->isString()->no()) {
                    // keyType is checked by ContextKeyRule
                    // @codeCoverageIgnoreStart
                    continue; // @codeCoverageIgnoreEnd
                }

                $contextKeys[] = $keyType->getValue();
            }

            foreach ($placeholders as $i => $placeholder) {
                if (in_array($placeholder, $contextKeys, true)) {
                    unset($checkBraces[$i]);
                }
            }

            if (count($checkBraces) === 0) {
                continue;
            }

            return RuleErrorBuilder::message(
                sprintf(self::ERROR_MISSED_KEY, $methodName, implode(',', $checkBraces))
            )->identifier('sfp-psr-log.placeholderCorrespondToKeysMissedKey')->build();
        }

        return null;
    }
}
