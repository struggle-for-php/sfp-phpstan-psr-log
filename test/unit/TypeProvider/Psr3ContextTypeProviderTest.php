<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\TypeProvider;

use Exception;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use Sfp\PHPStan\Psr\Log\TypeProvider\Psr3ContextTypeProvider;
use Throwable;

/**
 * eg.
 * ```
 * [@]param array{} $context
 * public function testLog(array $context) {
 *  $this->logger->info('foo, $context);
 * }
 * ```
 */
final class Psr3ContextTypeProviderTest extends AbstractContextTypeProviderTestCase
{
    /**
     * @dataProvider \SfpTest\PHPStan\Psr\Log\TypeProvider\GeneralContextTypeDataProvider::provideTypes
     */
    public function testTypeProviderWithDefaultThrowable(ConstantArrayType $argType, bool $expected): void
    {
        $provider = new Psr3ContextTypeProvider();

        self::assertSame($expected, $provider->getType()->accepts($argType, true)->yes());
    }

    /**
     * @dataProvider provideExceptionTypes
     */
    public function testTypeProviderWithException(ConstantArrayType $argType, bool $expected): void
    {
        $provider = new Psr3ContextTypeProvider(Exception::class);

        self::assertSame($expected, $provider->getType()->accepts($argType, true)->yes());
    }

    /**
     * @phpstan-return array<string, array{0: ConstantArrayType, 1: bool}>
     */
    public static function provideExceptionTypes(): array
    {
        return [
            "array{exception?: \Throwable}" => [
                new ConstantArrayType(
                    [new ConstantStringType('exception')],
                    [new ObjectType(Throwable::class)],
                    [0],
                    [0]
                ),
                false,
            ],
            "array{exception?: \Exception}" => [
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
