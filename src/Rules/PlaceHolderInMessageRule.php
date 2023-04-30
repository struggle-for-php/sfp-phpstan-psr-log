<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;

use function count;
use function implode;
use function in_array;
use function preg_match;
use function preg_match_all;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class PlaceHolderInMessageRule implements Rule
{
    private const ERROR_DOUBLE_BRACES              = 'Parameter $message of logger method Psr\Log\LoggerInterface::%s() should not includes double braces. - %s';
    private const ERROR_INVALID_CHAR               = 'Parameter $message of logger method Psr\Log\LoggerInterface::%s() has braces. But it includes invalid characters for placeholder. - %s';
    private const ERROR_NO_WHITESPACE_BETWEEN_WORD = 'Parameter $message of logger method Psr\Log\LoggerInterface::%s() has braces. There should be whitespace between placeholder and word.';

    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    /**
     * @param Node\Expr\MethodCall $node
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

        $messageArgumentNo = 0;
        if ($methodName === 'log') {
            if (
                count($args) < 2
                || ! $args[0] instanceof Node\Arg
                || ! $args[0]->value instanceof Node\Scalar\String_
            ) {
                return [];
            }

            $messageArgumentNo = 1;
        } elseif (! in_array($methodName, LogLevelListInterface::LOGGER_LEVEL_METHODS)) {
            return [];
        }

        $message = $args[$messageArgumentNo];

        if (! $message->value instanceof Node\Scalar\String_) {
            return [];
        }

        $errors = [];

        $errors += self::checkDoubleBrace($message->value->value, $methodName);
        $errors += self::checkInvalidChar($message->value->value, $methodName);
        $errors += self::checkNoSpaceBetweenWord($message->value->value, $methodName);

        return $errors;
    }

    private static function checkDoubleBrace(string $message, string $methodName): array
    {
        $matched = preg_match_all('#{{(.+?)}}#', $message, $matches);

        if ($matched === 0 || $matched === false) {
            return [];
        }

        return [sprintf(self::ERROR_DOUBLE_BRACES, $methodName, implode(',', $matches[0]))];
    }

    private static function checkInvalidChar(string $message, string $methodName): array
    {
        $matched = preg_match_all('#{(.+?)}#', $message, $matches);

        if ($matched === 0 || $matched === false) {
            return [];
        }

        $invalidPlaceHolders = [];
        foreach ($matches[1] as $i => $placeholderCandidate) {
            if (preg_match('#\A[A-Za-z0-9_\.]+\z#', $placeholderCandidate) === 0) {
                $invalidPlaceHolders[$i] = $matches[0][$i];
            }
        }

        if (count($invalidPlaceHolders) === 0) {
            return [];
        }

        return [sprintf(self::ERROR_INVALID_CHAR, $methodName, implode(',', $invalidPlaceHolders))];
    }

    private static function checkNoSpaceBetweenWord(string $message, string $methodName): array
    {
        preg_match('#\A.*?([\w]+?)*{[A-Za-z0-9_\.]+}([\w]+?)*.*\z#', $message, $matches);

        if (count($matches) < 2) {
            return [];
        }

        return [sprintf(self::ERROR_NO_WHITESPACE_BETWEEN_WORD, $methodName)];
    }
}