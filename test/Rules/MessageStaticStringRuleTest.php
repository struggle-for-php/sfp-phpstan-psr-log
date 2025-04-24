<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\MessageStaticStringRule;

/**
 * @extends RuleTestCase<MessageStaticStringRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\MessageStaticStringRule
 */
final class MessageStaticStringRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MessageStaticStringRule();
    }

    /**
     * @test
     */
    public function testProcessNode(): void
    {
        $this->analyse([__DIR__ . '/data/messageMustBeStatic.php'], [
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string',
                16, // variable
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string',
                17, // double quote escaped with braces
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string',
                18, // double quote escaped without braces
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string',
                19, // concat double quote
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string',
                20, // concat single quote
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string',
                21, // function call
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::log() is not a static string',
                23, // call log() method with variable
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
            [
                'Parameter $message of logger method Psr\Log\LoggerInterface::info() is not a static string',
                25,
                'See https://www.php-fig.org/psr/psr-3/meta/#static-log-messages',
            ],
        ]);
    }
}
