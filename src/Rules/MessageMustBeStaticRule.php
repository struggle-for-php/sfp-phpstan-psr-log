<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;

use function count;
use function get_class;
use function in_array;
use function sprintf;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class MessageMustBeStaticRule implements Rule
{
    private const ERROR_MESSAGE_NOT_STATIC = 'Parameter $message of logger method Psr\Log\LoggerInterface::%s() is not a static string - %s';

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

        $messageArgumentNo = 0;
        if ($methodName === 'log') {
            if (
                count($args) < 2
                || ! $args[0]->value instanceof Node\Scalar\String_
            ) {
                // @codeCoverageIgnoreStart
                return []; // @codeCoverageIgnoreEnd
            }

            $messageArgumentNo = 1;
        } elseif (! in_array($methodName, LogLevelListInterface::LOGGER_LEVEL_METHODS)) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        $message = $args[$messageArgumentNo];

        if (! $message->value instanceof Node\Scalar\String_) {
            return [sprintf(self::ERROR_MESSAGE_NOT_STATIC, $methodName, get_class($message->value))];
        }

        return [];
    }
}
