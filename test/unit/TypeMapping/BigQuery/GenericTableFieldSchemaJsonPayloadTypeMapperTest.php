<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\TypeMapping\BigQuery;

use PHPUnit\Framework\TestCase;
use Sfp\PHPStan\Psr\Log\TypeMapping\BigQuery\GenericTableFieldSchemaJsonPayloadTypeMapper;

/**
 * @covers \Sfp\PHPStan\Psr\Log\TypeMapping\BigQuery\GenericTableFieldSchemaJsonPayloadTypeMapper
 */
final class GenericTableFieldSchemaJsonPayloadTypeMapperTest extends TestCase
{
    /**
     * @see https://github.com/phpstan/phpstan-src/blob/2.1.1/tests/PHPStan/Type/Constant/ConstantArrayTypeBuilderTest.php
     */
    public function testConvertFieldsToTypes(): void
    {
        $type = GenericTableFieldSchemaJsonPayloadTypeMapper::convertFieldsToTypes([
            ['name' => '', 'type' => 'STRING'],
            ['name' => '0', 'type' => 'STRING'],
        ]);

        self::assertSame([0, 1], $type->getNextAutoIndexes());
    }
}
