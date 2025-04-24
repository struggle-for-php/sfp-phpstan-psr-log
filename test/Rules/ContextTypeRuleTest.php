<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;
use Sfp\PHPStan\Psr\Log\Rules\ContextTypeRule;
use Sfp\PHPStan\Psr\Log\TypeMapping\BigQuery\GenericTableFieldSchemaJsonPayloadTypeMapper;
use Sfp\PHPStan\Psr\Log\TypeProvider\BigQueryContextTypeProvider;
use Sfp\PHPStan\Psr\Log\TypeProviderResolver\AnyScopeContextTypeProviderResolver;
use Sfp\PHPStan\Psr\Log\TypeProviderResolver\ContextTypeProviderResolverInterface;

use function sprintf;

/**
 * @extends RuleTestCase<ContextTypeRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\ContextTypeRule
 */
final class ContextTypeRuleTest extends RuleTestCase
{
    /** @var bool */
    private $checkUnionTypes = true;

    /** @var null|ContextTypeProviderResolverInterface */
    private $contextTypeProviderResolver;

    protected function getRule(): Rule
    {
        $reflectionProvider = self::createReflectionProvider();

        return new ContextTypeRule(
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
            ),
            $this->contextTypeProviderResolver
        );
    }

    /**
     * @dataProvider provideContextTypePattern
     * @phpstan-param list<array{0: string, 1: int}> $expectedErrors
     */
    public function testProcessNode(bool $checkUnionTypes, array $expectedErrors): void
    {
        $this->contextTypeProviderResolver = null;

        $this->checkUnionTypes = $checkUnionTypes;
        $this->analyse([__DIR__ . '/data/contextType.php'], $expectedErrors);
    }

    /**
     * @phpstan-return list<array{checkUnionTypes: bool, expectedErrors: list<array{0: string, 1: int}>}>
     */
    public static function provideContextTypePattern(): array
    {
        $expectedError = [
            'Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects array{exception?: Throwable}, array{exception: string} given.',
            19,
        ];

        return [
            [
                'checkUnionTypes' => false,
                'expectedErrors'  => [
                    $expectedError,
                ],
            ],
            [
                'checkUnionTypes' => true,
                'expectedErrors'  => [
                    $expectedError,
                    [
                        'Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects array{exception?: Throwable}, (array{exception: string} | array{exception: Throwable}) given.',
                        20,
                    ],
                ],
            ],
        ];
    }

     /**
      * @test
      */
    public function testProcessNodeWithBigQueryContextTypeProvider(): void
    {
        $contextTypeProvider               = new BigQueryContextTypeProvider(__DIR__ . '/../TypeProvider/data/bigQuerySchema.json', new GenericTableFieldSchemaJsonPayloadTypeMapper());
        $this->contextTypeProviderResolver = new AnyScopeContextTypeProviderResolver($contextTypeProvider);
        $this->analyse([__DIR__ . '/data/contextType.php'], [
            [
                sprintf(
                    'Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects %s, array{exception: string} given.',
                    'array{first_name?: string, product?: array{id?: string}, cancellation_reason?: (float | int | numeric-string), cancellation_date?: \DateTimeInterface, exception?: \Throwable}'
                ),
                19,
            ],
            [
                'Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects array{first_name?: string, product?: array{id?: string}, cancellation_reason?: (float | int | numeric-string), cancellation_date?: \DateTimeInterface, exception?: \Throwable}, (array{exception: string} | array{exception: Throwable}) given.',
                20,
            ],
        ]);
    }
}
