<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\TypeProvider;

use Exception;
use Override;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use RuntimeException;
use Sfp\PHPStan\Psr\Log\Internal\UnsealedConstantArrayShape;
use Sfp\PHPStan\Psr\Log\TypeMapping\BigQuery\TableFieldSchemaJsonPayloadTypeMapperInterface;
use UnexpectedValueException;

use function file_get_contents;
use function is_array;
use function json_decode;
use function sprintf;

/**
 * @experimental
 * @phpstan-import-type schema_item from TableFieldSchemaJsonPayloadTypeMapperInterface
 */
final class BigQueryContextTypeProvider implements ContextTypeProviderInterface
{
    private string $schemaFile;

    private TableFieldSchemaJsonPayloadTypeMapperInterface $tableFieldSchemaJsonPayloadTypeMapper;

    /** @phpstan-var ?list<schema_item> */
    private ?array $jsonPayloadFields = null;

    public function __construct(
        string $schemaFile,
        TableFieldSchemaJsonPayloadTypeMapperInterface $tableFieldSchemaJsonPayloadTypeMapper
    ) {
        $this->schemaFile                            = $schemaFile;
        $this->tableFieldSchemaJsonPayloadTypeMapper = $tableFieldSchemaJsonPayloadTypeMapper;
    }

    #[Override]
    public function getType(): Type
    {
        $builder = ConstantArrayTypeBuilder::createFromConstantArray(
            $this->tableFieldSchemaJsonPayloadTypeMapper->toArrayType($this->getJsonPayloadFields())
        );

        $builder->setOffsetValueType(
            new ConstantStringType('exception'),
            new ObjectType('\Throwable'),
            true
        );

        $array          = $builder->getArray();
        $constantArrays = $array->getConstantArrays();

        return $constantArrays === [] ? $array : UnsealedConstantArrayShape::apply($constantArrays[0]);
    }

    /**
     * @phpstan-return list<schema_item>
     */
    private function getJsonPayloadFields(): array
    {
        if (! isset($this->jsonPayloadFields)) {
            $schemaJson = file_get_contents($this->schemaFile);
            if ($schemaJson === false) {
                throw new RuntimeException(sprintf('File %s cant open', $this->schemaFile));
            }

            $schema = json_decode($schemaJson, true);

            if (! is_array($schema)) {
                throw new RuntimeException('schema is not array');
            }

            $jsonPayloadFields = null;
            foreach ($schema as $item) {
                if (! is_array($item)) {
                    throw new RuntimeException('item is not array');
                }

                if (! isset($item['name']) || $item['name'] !== 'jsonPayload') {
                    continue;
                }

                if (! isset($item['fields']) || ! is_array($item['fields'])) {
                    throw new UnexpectedValueException('fields is not array');
                }

                $jsonPayloadFields = $item['fields'];
            }

            if ($jsonPayloadFields === null) {
                throw new Exception('schemaFile must have jsonPayload field');
            }

            /**
             * @todo validate list<schema_item>
             * @phpstan-var list<schema_item> $jsonPayloadFields
             */
            $this->jsonPayloadFields = $jsonPayloadFields;
        }

        return $this->jsonPayloadFields;
    }
}
