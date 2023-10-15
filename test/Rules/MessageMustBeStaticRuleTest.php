<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\MessageMustBeStaticRule;

/**
 * @implements RuleTestCase<MessageMustBeStaticRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\MessageMustBeStaticRule
 */
final class MessageMustBeStaticRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MessageMustBeStaticRule();
    }

    /**
     * @test
     */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/messageMustBeStatic.php'], [
            'variable'                            => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Expr\Variable',
                10,
            ],
            'double quote escaped with braces'    => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Scalar\Encapsed',
                11,
            ],
            'double quote escaped without braces' => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Scalar\Encapsed',
                12,
            ],
            'concat double quote'                 => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Expr\BinaryOp\Concat',
                13,
            ],
            'concat single quote'                 => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Expr\BinaryOp\Concat',
                14,
            ],
            'function call'                       => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Expr\FuncCall',
                15,
            ],
            'call log() method'                   => [
                'Parameter $message of logger method Psr\Log\LoggerInterface::log() is not a static string - PhpParser\Node\Expr\Variable',
                17,
            ],
        ]);
    }
}
