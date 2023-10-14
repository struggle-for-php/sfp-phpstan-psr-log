<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextKeyRule;

/**
 * @extends RuleTestCase<ContextKeyRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\ContextKeyRule
 */
final class ContextKeyOriginalPatternRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ContextKeyRule('#\A[A-Za-z0-9-]+\z#');
    }

    /** @test */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/contextKey_originalPattern.php'], [
            [
                'Parameter $context of logger method Psr\Log\LoggerInterface::info(), key should be match #\A[A-Za-z0-9-]+\z#.',
                14, // empty string
            ],
        ]);
    }
}
