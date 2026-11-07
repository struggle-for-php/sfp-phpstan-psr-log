<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\TypeProvider;

use Exception;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Sfp\PHPStan\Psr\Log\TypeMapping\BigQuery\GenericTableFieldSchemaJsonPayloadTypeMapper;
use Sfp\PHPStan\Psr\Log\TypeProvider\BigQueryContextTypeProvider;

final class BigQueryContextTypeProviderTest extends AbstractContextTypeProviderTestCase
{
    /**
     * @dataProvider \SfpTest\PHPStan\Psr\Log\TypeProvider\GeneralContextTypeDataProvider::provideTypes
     */
    public function testGeneralContextType(ConstantArrayType $argType, bool $expected): void
    {
        $provider = new BigQueryContextTypeProvider(
            __DIR__ . '/data/bigQuerySchema.json',
            new GenericTableFieldSchemaJsonPayloadTypeMapper()
        );
        self::assertSame($expected, $provider->getType()->accepts($argType, true)->yes());
    }

    /**
     * @dataProvider provideTypes
     */
    public function testAgainstBigQuerySchema(ConstantArrayType $argType, bool $expected): void
    {
        $provider = new BigQueryContextTypeProvider(
            __DIR__ . '/data/bigQuerySchema.json',
            new GenericTableFieldSchemaJsonPayloadTypeMapper()
        );

        $argType = new ConstantArrayType(
            [new ConstantStringType('first_name')],
            [new ObjectType(Exception::class)],
            [0],
            [0]
        );

        self::assertFalse($provider->getType()->accepts($argType, true)->yes());
    }

    /**
     * @phpstan-return array<string, array{0: ConstantArrayType, 1: bool}>
     */
    public static function provideTypes(): array
    {
        return [
            "array{first_name?: string}"    => [
                new ConstantArrayType(
                    [new ConstantStringType('first_name')],
                    [new StringType()],
                    [0],
                    [0]
                ),
                true,
            ],
            "array{first_name?: Exception}" => [
                new ConstantArrayType(
                    [new ConstantStringType('first_name')],
                    [new ObjectType(Exception::class)],
                    [0],
                    [0]
                ),
                false,
            ],
        ];
    }
}
