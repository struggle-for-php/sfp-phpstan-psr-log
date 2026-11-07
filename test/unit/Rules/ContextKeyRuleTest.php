<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use LogicException;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Assert;
use Sfp\PHPStan\Psr\Log\Rules\ContextKeyRule;

use const PHP_VERSION_ID;

/**
 * @extends RuleTestCase<ContextKeyRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\ContextKeyRule
 */
final class ContextKeyRuleTest extends RuleTestCase
{
    private ?string $contextKeyOriginalPattern;

    protected function getRule(): Rule
    {
        return new ContextKeyRule($this->contextKeyOriginalPattern);
    }

    public function testAlwaysShouldBeCheckedNonEmptyString(): void
    {
        $this->contextKeyOriginalPattern = null;
        $this->analyse([__DIR__ . '/data/contextKey_nonEmptyString.php'], [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                22, // empty string
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                23, // integer
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                25, // not specified
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::log(), key should be non empty string.',
                26, // log method call
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                29, // union parameter
            ],
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                34, // inline array
            ],
        ]);
    }

    public function testAlwaysShouldBeCheckedNonEmptyStringWithFloatKey(): void
    {
        if (PHP_VERSION_ID >= 80600) {
            Assert::markTestSkipped('Since PHP 8.6, casting a float key not representable as an int emits a warning.');
        }

        $this->contextKeyOriginalPattern = null;
        $this->analyse([__DIR__ . '/data/contextKey_nonEmptyString_floatKey.php'], [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be non empty string.',
                11, // DNumber
            ],
        ]);
    }

    public function testOriginalPattern(): void
    {
        $this->contextKeyOriginalPattern = '#\A[A-Za-z0-9-]+\z#';
        $this->analyse([__DIR__ . '/data/contextKey_originalPattern.php'], [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be match #\A[A-Za-z0-9-]+\z#.',
                16,
            ],
        ]);
    }

    public function testEmptyStringPattern(): void
    {
        $this->contextKeyOriginalPattern = '';
        $this->expectException(LogicException::class);
        $this->gatherAnalyserErrors([__DIR__ . '/data/contextKey_originalPattern.php']);
    }

    public function testWithBadRegex(): void
    {
        $this->contextKeyOriginalPattern = '#\A[A-Za-z0-9-]+';
        $this->expectException(LogicException::class);
        $this->gatherAnalyserErrors([__DIR__ . '/data/contextKey_originalPattern.php']);
    }
}
