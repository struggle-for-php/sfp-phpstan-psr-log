<?php

namespace SfpTest\PHPStan\Psr\Log;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\FileAnalyser;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\File\FileHelper;
use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\PhpDoc\PhpDocNodeResolver;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Testing\TestCase;
use PHPStan\Type\FileTypeMapper;

abstract class AbstractTest extends TestCase
{
    /** @var \PHPStan\Analyser\Analyser|null */
    private $analyser;

    abstract public function provideExpectErrors() : array ;

    /**
     * @test
     * @dataProvider provideExpectErrors
     */
    final public function shouldRaiseErrorForMisleadUsageOfContextException(string $expects) : void
    {
        $analyser = $this->getAnalyser();

        $files = [
            __DIR__ . '/Asset/LoggerCall.php',
            __DIR__ . '/Asset/mislead_usage_of_context_exception.php'
        ];

        $files = \array_map([$this->getFileHelper(), 'normalizePath'], $files);

        $actualErrors = $analyser->analyse($files);

        $errors = '';
        foreach($actualErrors->getErrors() as $error) {
            assert($error instanceof Error);
            $errors .= $error->getMessage() . "\n";
        }

        $this->assertSame($expects, $errors);
    }

    final protected function getAnalyser(): Analyser
    {
        if ($this->analyser === null) {
            $registry = self::getContainer()->getService('registry');
            $broker = $this->createBroker();
            $printer = new \PhpParser\PrettyPrinter\Standard();
            $fileHelper = $this->getFileHelper();
            $typeSpecifier = $this->createTypeSpecifier(
                $printer,
                $broker,
                [],
                []
            );
            $currentWorkingDirectory = $this->getCurrentWorkingDirectory();
            $nodeScopeResolver = new NodeScopeResolver(
                $broker,
                $this->getParser(),
                new FileTypeMapper(
                    $this->getParser(),
                    self::getContainer()->getByType(PhpDocStringResolver::class),
                    self::getContainer()->getByType(PhpDocNodeResolver::class),
                    $this->createMock(Cache::class),
                    new AnonymousClassNameHelper(new FileHelper($currentWorkingDirectory),new FuzzyRelativePathHelper($currentWorkingDirectory, []))
                ),
                $fileHelper,
                $typeSpecifier,
                false,
                false,
                true,
                [],
                []
            );
            $fileAnalyser = new FileAnalyser(
                $this->createScopeFactory($broker, $typeSpecifier),
                $nodeScopeResolver,
                $this->getParser(),
                new DependencyResolver($broker),
                $fileHelper,
                true
            );
            $this->analyser = new Analyser(
                $fileAnalyser,
                $registry,
                $nodeScopeResolver,
                50
            );
        }

        return $this->analyser;
    }

}