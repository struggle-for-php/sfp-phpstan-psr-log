<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\TypeProvider;

use Override;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Sfp\PHPStan\Psr\Log\Internal\UnsealedConstantArrayShape;
use Throwable;

final class Psr3ContextTypeProvider implements ContextTypeProviderInterface
{
    /** @var class-string */
    private string $exceptionClass;

    /** @param class-string $exceptionClass */
    public function __construct(string $exceptionClass = Throwable::class)
    {
        $this->exceptionClass = $exceptionClass;
    }

    #[Override]
    public function getType(): Type
    {
        return UnsealedConstantArrayShape::apply(new ConstantArrayType(
            [new ConstantStringType('exception')],
            [new ObjectType($this->exceptionClass)],
            [0],
            [0]
        ));
    }
}
