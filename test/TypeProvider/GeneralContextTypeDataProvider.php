<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\TypeProvider;

use Exception;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use Throwable;

class GeneralContextTypeDataProvider
{
    /**
     * @phpstan-return array<string, array{0: ConstantArrayType, 1: bool}>
     */
    public static function provideTypes(): array
    {
        return [
            'array{}'                        => [
                new ConstantArrayType([], []),
                true,
            ],
            "array{non-exception: 'string'}" => [
                new ConstantArrayType(
                    [new ConstantStringType('non-exception')],
                    [new ConstantStringType('string')]
                ),
                true,
            ],
            "array{exception?: 'string'}"    => [
                new ConstantArrayType(
                    [new ConstantStringType('exception')],
                    [new ConstantStringType('string')],
                    [0],
                    [0]
                ),
                false,
            ],
            "array{exception?: \Throwable}"  => [
                new ConstantArrayType(
                    [new ConstantStringType('exception')],
                    [new ObjectType(Throwable::class)],
                    [0],
                    [0]
                ),
                true,
            ],
            "array{exception?: \Exception}"  => [
                new ConstantArrayType(
                    [new ConstantStringType('exception')],
                    [new ObjectType(Exception::class)],
                    [0],
                    [0]
                ),
                true,
            ],
        ];
    }
}
