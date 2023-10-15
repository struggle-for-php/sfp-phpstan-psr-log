<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\MessageMustBeStaticRule;

/**
 * @extends  RuleTestCase<MessageMustBeStaticRule>
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
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Expr\Variable',
                10, // variable
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Scalar\Encapsed',
                11, // double quote escaped with braces
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Scalar\Encapsed',
                12, // double quote escaped without braces
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Expr\BinaryOp\Concat',
                13, // concat double quote
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Expr\BinaryOp\Concat',
                14, // concat single quote
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string - PhpParser\Node\Expr\FuncCall',
                15, // function call
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::log() is not a static string - PhpParser\Node\Expr\Variable',
                17, // call log() method
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
        ]);
    }
}
