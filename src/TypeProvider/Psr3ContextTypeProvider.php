<?php

declare(strict_types=1);

namespace Sfp\PHPStan\Psr\Log\TypeProvider;

use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Throwable;

final class Psr3ContextTypeProvider implements ContextTypeProviderInterface
{
    /** @var string */
    private $exceptionClass;

    public function __construct(string $exceptionClass = Throwable::class)
    {
        $this->exceptionClass = $exceptionClass;
    }

    public function getType(): Type
    {
        return new ConstantArrayType(
            [new ConstantStringType('exception')],
            [new ObjectType($this->exceptionClass)],
            [0],
            [0]
        );
    }
}
