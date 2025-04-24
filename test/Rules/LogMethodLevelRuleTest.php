<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\LogMethodLevelRule;

/**
 * @extends RuleTestCase<LogMethodLevelRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\LogMethodLevelRule
 */
final class LogMethodLevelRuleTest extends RuleTestCase
{
    /** @var bool */
    private $checkUnionTypes = true;

    protected function getRule(): Rule
    {
        $reflectionProvider = self::createReflectionProvider();

        return new LogMethodLevelRule(
            /** @phpstan-ignore phpstanApi.constructor */
            new RuleLevelHelper(
                $reflectionProvider,
                true,
                false,
                $this->checkUnionTypes,
                false,
                false,
                true,
                true
            )
        );
    }

    /**
     * @dataProvider provideLogMethodLevelPattern
     * @phpstan-param list<array{0: string, 1: int}> $expectedErrors
     */
    public function testLogMethodLevel(bool $checkUnionTypes, array $expectedErrors): void
    {
        $this->checkUnionTypes = $checkUnionTypes;
        $this->analyse([__DIR__ . '/data/logMethodLevel.php'], $expectedErrors);
    }

    /**
     * @phpstan-return list<array{checkUnionTypes: bool, expectedErrors: list<array{0: string, 1: int}>}>
     */
    public static function provideLogMethodLevelPattern(): array
    {
        return [
            [
                'checkUnionTypes' => true,
                'expectedErrors'  => [
                    [
                        "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects ('alert' | 'critical' | 'debug' | 'emergency' | 'error' | 'info' | 'notice' | 'warning'), 'panic' given.",
                        23,
                    ],
                    [
                        "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects ('alert' | 'critical' | 'debug' | 'emergency' | 'error' | 'info' | 'notice' | 'warning'), string given.",
                        24,
                    ],
                    [
                        "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects ('alert' | 'critical' | 'debug' | 'emergency' | 'error' | 'info' | 'notice' | 'warning'), ('foo' | 'info' | 'panic') given.",
                        25,
                    ],
                    [
                        "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects ('alert' | 'critical' | 'debug' | 'emergency' | 'error' | 'info' | 'notice' | 'warning'), 100 given.",
                        26,
                    ],
                ],
            ],
            [
                'checkUnionTypes' => false,
                'expectedErrors'  => [
                    [
                        "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects ('alert' | 'critical' | 'debug' | 'emergency' | 'error' | 'info' | 'notice' | 'warning'), 'panic' given.",
                        23,
                    ],
                    [
                        "Parameter #1 \$level of method Psr\Log\LoggerInterface::log() expects ('alert' | 'critical' | 'debug' | 'emergency' | 'error' | 'info' | 'notice' | 'warning'), 100 given.",
                        26,
                    ],
                ],
            ],
        ];
    }
}
