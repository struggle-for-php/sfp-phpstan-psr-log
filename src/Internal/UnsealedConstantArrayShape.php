<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\Internal;

use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;

use function method_exists;

/**
 * Marks a {@see ConstantArrayType} produced via the PHPStan API as unsealed
 * (the {@code array{foo?: Bar, ...}} form).
 *
 * PHPStan 2.2 distinguishes sealed and unsealed array shapes; user-provided
 * log contexts almost always carry extra keys, so the expected context type
 * must permit them. On older PHPStan releases that do not expose the unsealed
 * API, the original type is returned unchanged — preserving prior behaviour.
 *
 * @internal
 */
final class UnsealedConstantArrayShape
{
    public static function apply(ConstantArrayType $type): ConstantArrayType
    {
        /** @phpstan-ignore function.alreadyNarrowedType, function.impossibleType */
        if (! method_exists(ConstantArrayTypeBuilder::class, 'makeUnsealed')) {
            return $type;
        }

        $builder = ConstantArrayTypeBuilder::createFromConstantArray($type);
        /** @phpstan-ignore method.notFound */
        $builder->makeUnsealed(
            (new BenevolentUnionType([new IntegerType(), new StringType()]))->toArrayKey(),
            new MixedType()
        );

        $constantArrays = $builder->getArray()->getConstantArrays();

        return $constantArrays === [] ? $type : $constantArrays[0];
    }
}
