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
    private const ERROR_DOUBLE_BRACES = 'Parameter $message of logger method Psr\Log\LoggerInterface::%s() should not includes double braces. - %s';
    private const ERROR_INVALID_CHAR  = 'Parameter $message of logger method Psr\Log\LoggerInterface::%s() has braces. But it includes invalid characters for placeholder. - %s';

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

        $messageArgumentNo = 0;
        if ($methodName === 'log') {
            if (
                count($args) < 2
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

        return $errors;
    }

    /**
     * @phpstan-return list<string>
     */
    private static function checkDoubleBrace(string $message, string $methodName): array
    {
        $matched = preg_match_all('#{{(.+?)}}#', $message, $matches);

        if ($matched === 0 || $matched === false) {
            return [];
        }

        return [sprintf(self::ERROR_DOUBLE_BRACES, $methodName, implode(',', $matches[0]))];
    }

    /**
     * @phpstan-return list<string>
     */
    private static function checkInvalidChar(string $message, string $methodName): array
    {
        $matched = preg_match_all('#{(.+?)}#', $message, $matches);

        if ($matched === 0 || $matched === false) {
            return [];
        }

        $invalidPlaceHolders = [];
        foreach ($matches[1] as $i => $placeholderCandidate) {
            if (preg_match('#\A[A-Za-z0-9_\.]+\z#', $placeholderCandidate) !== 1) {
                $invalidPlaceHolders[$i] = $matches[0][$i];
            }
        }

        if (count($invalidPlaceHolders) === 0) {
            return [];
        }

        return [sprintf(self::ERROR_INVALID_CHAR, $methodName, implode(',', $invalidPlaceHolders))];
    }
}
