<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;

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

    private const ERROR_ORIGINAL_PATTERN_BAD = 'Your contextKeyOriginalPattern %s seems not valid regex. Failed.';

    private const ERROR_NOT_MATCH_ORIGINAL_PATTERN = 'Parameter $context of logger method Psr\Log\LoggerInterface::%s(), key should be match %s.';

    /** @var bool */
    private $treatPhpDocTypesAsCertain;

    /** @var string|null */
    private $contextKeyOriginalPattern;

    public function __construct(
        bool $treatPhpDocTypesAsCertain,
        ?string $contextKeyOriginalPattern = null
    ) {
        $this->treatPhpDocTypesAsCertain = $treatPhpDocTypesAsCertain;
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
            if (count($args) < 2) {
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

        if ($this->treatPhpDocTypesAsCertain) {
            $arrayType = $scope->getType($context->value);
        } else {
            $arrayType = $scope->getNativeType($context->value);
        }

        if ($arrayType->isIterableAtLeastOnce()->no()) {
            // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnoreEnd
        }

        $constantArrays = $arrayType->getConstantArrays();

        if (count($constantArrays) === 0) {
            return [];
        }

        $errors = self::keysAreNonEmptyString($constantArrays, $methodName);

        if ($errors !== []) {
            return $errors;
        }

        return self::originalPatternMatches($constantArrays, $methodName);
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
     * @phpstan-param list<\PHPStan\Type\Constant\ConstantArrayType> $constantArrays
     * phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
     * @phpstan-return list<\PHPStan\Rules\RuleError>
     */
    private static function keysAreNonEmptyString(array $constantArrays, string $methodName): array
    {
        $errors = [];
        foreach ($constantArrays as $constantArray) {
            foreach ($constantArray->getKeyTypes() as $keyType) {
                if (! $keyType instanceof ConstantStringType) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(self::ERROR_NOT_NON_EMPTY_STRING, $methodName)
                    )->identifier('sfp-psr-log.contextKeyNonEmptyString')->build();
                    continue;
                }

                if ($keyType->getValue() === '') {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(self::ERROR_NOT_NON_EMPTY_STRING, $methodName)
                    )->identifier('sfp-psr-log.contextKeyNonEmptyString')->build();
                }
            }
        }

        return $errors;
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
     * @phpstan-param list<\PHPStan\Type\Constant\ConstantArrayType> $constantArrays
     * phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
     * @phpstan-return list<\PHPStan\Rules\RuleError>
     */
    private function originalPatternMatches(array $constantArrays, string $methodName): array
    {
        if (! $this->contextKeyOriginalPattern) {
            return [];
        }

        $errors = [];
        foreach ($constantArrays as $constantArray) {
            foreach ($constantArray->getKeyTypes() as $keyType) {
                if (! $keyType instanceof ConstantStringType) {
                    continue;
                }

                $matched = preg_match($this->contextKeyOriginalPattern, $keyType->getValue(), $matches);

                if ($matched === false) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(self::ERROR_ORIGINAL_PATTERN_BAD, $this->contextKeyOriginalPattern)
                    )->identifier('sfp-psr-log.contextKeyOriginalPatternBadRegex')->build();
                }

                if ($matched === 0) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(self::ERROR_NOT_MATCH_ORIGINAL_PATTERN, $methodName, $this->contextKeyOriginalPattern)
                    )->identifier('sfp-psr-log.contextKeyOriginalPattern')->build();
                }
            }
        }

        return $errors;
    }
}
