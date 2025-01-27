<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\Rules;

use PHPStan\Rules\Rule;
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
    /** @var null|ContextTypeProviderResolverInterface */
    private $contextTypeProviderResolver;

    protected function getRule(): Rule
    {
        return new ContextTypeRule($this->contextTypeProviderResolver);
    }

    /**
     * @test
     */
    public function testProcessNode(): void
    {
        $this->contextTypeProviderResolver = null;
        $this->analyse([__DIR__ . '/data/contextType.php'], [
            [
                sprintf(
                    'Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects %s, array{exception: string} given.',
                    'array{exception?: Throwable}'
                ),
                14,
            ],
        ]);
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
                14,
            ],
        ]);
    }
}
