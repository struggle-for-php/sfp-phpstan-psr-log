<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use Sfp\PHPStan\Psr\Log\Rules\ContextTypeRule;
use Sfp\PHPStan\Psr\Log\TypeMapping\BigQuery\GenericTableFieldSchemaJsonPayloadTypeMapper;
use Sfp\PHPStan\Psr\Log\TypeProvider\BigQueryContextTypeProvider;
use Sfp\PHPStan\Psr\Log\TypeProviderResolver\AnyScopeContextTypeProviderResolver;
use Sfp\PHPStan\Psr\Log\TypeProviderResolver\ContextTypeProviderResolverInterface;

use function method_exists;
use function sprintf;

/**
 * @extends RuleTestCase<ContextTypeRule>
 * @covers \Sfp\PHPStan\Psr\Log\Rules\ContextTypeRule
 */
final class ContextTypeRuleTest extends RuleTestCase
{
    private bool $checkUnionTypes = true;

    private ?ContextTypeProviderResolverInterface $contextTypeProviderResolver;

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
        $expected = self::expectedContextShape();

        $expectedError = [
            <<<EOF
Parameter #2 \$context of method Psr\Log\LoggerInterface::info() expects $expected, array{exception: string} given.
    💡 Offset 'exception' (Throwable) does not accept type string.
EOF,
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
                        <<<EOF
Parameter #2 \$context of method Psr\Log\LoggerInterface::info() expects $expected, (array{exception: string} | array{exception: Throwable}) given.
    💡 Offset 'exception' (Throwable) does not accept type string.
EOF,
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
        $expected                          = self::expectedBigQueryShape();
        $this->analyse([__DIR__ . '/data/contextType.php'], [
            [
                sprintf(
                    <<<'EOF'
Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects %s, %s given.
    💡 Offset 'exception' (Throwable) does not accept type string.
EOF,
                    $expected,
                    'array{exception: string}'
                ),
                19,
            ],
            [
                sprintf(
                    <<<'EOF'
Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects %s, %s given.
    💡 Offset 'exception' (Throwable) does not accept type string.
EOF,
                    $expected,
                    '(array{exception: string} | array{exception: Throwable})'
                ),
                20,
            ],
        ]);
    }

    private static function expectedContextShape(): string
    {
        return self::unsealedShapesSupported()
            ? 'array{exception?: Throwable, ...}'
            : 'array{exception?: Throwable}';
    }

    private static function expectedBigQueryShape(): string
    {
        return self::unsealedShapesSupported()
            ? 'array{first_name?: string, product?: array{id?: string, ...}, cancellation_reason?: (float | int | numeric-string), cancellation_date?: \DateTimeInterface, exception?: \Throwable, ...}'
            : 'array{first_name?: string, product?: array{id?: string}, cancellation_reason?: (float | int | numeric-string), cancellation_date?: \DateTimeInterface, exception?: \Throwable}';
    }

    private static function unsealedShapesSupported(): bool
    {
        /** @phpstan-ignore function.alreadyNarrowedType, function.impossibleType */
        return method_exists(ConstantArrayTypeBuilder::class, 'makeUnsealed');
    }
}
