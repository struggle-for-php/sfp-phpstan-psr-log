<?php

declare(strict_types=1);

namespace SfpTest\PHPStan\Psr\Log\TypeProvider;

use PHPStan\Reflection\ReflectionProviderStaticAccessor;
use PHPStan\Testing\PHPStanTestCase;

abstract class AbstractContextTypeProviderTestCase extends PHPStanTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        /** @phpstan-ignore phpstanApi.method */
        ReflectionProviderStaticAccessor::registerInstance($this->createReflectionProvider());
    }
}
