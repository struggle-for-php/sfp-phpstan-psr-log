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
final class ContextKeyOriginalPatternBadRegexRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ContextKeyRule('#\A[A-Za-z0-9-]+');
    }

    /** @test */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/contextKey_originalPattern.php'], [
            [
                'Your contextKeyOriginalPattern #\A[A-Za-z0-9-]+ seems not valid regex. Failed.',
                8,
            ],
            [
                'Your contextKeyOriginalPattern #\A[A-Za-z0-9-]+ seems not valid regex. Failed.',
                9,
            ],
            [
                'Your contextKeyOriginalPattern #\A[A-Za-z0-9-]+ seems not valid regex. Failed.',
                14,
            ],
        ]);
    }
}
