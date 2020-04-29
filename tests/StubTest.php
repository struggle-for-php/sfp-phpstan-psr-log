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

class StubTest extends TestCase
{
    /** @var \PHPStan\Analyser\Analyser|null */
    private $analyser;

    /**
     * @test
     */
    public function shouldRaiseErrorForMisleadUsageOfContextException() : void
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

$expects = <<<'EXPECT'
Parameter #2 $context of method Psr\Log\LoggerInterface::emergency() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::alert() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::critical() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::error() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::warning() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::notice() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::info() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\LoggerInterface::debug() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::emergency() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::alert() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::critical() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::error() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::warning() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::notice() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::info() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method Psr\Log\AbstractLogger::debug() expects array()|array('exception' => Exception), array('exception' => 'foo') given.

EXPECT
;

        $this->assertSame($expects, $errors);

    }

    private function getAnalyser(): Analyser
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

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon'
        ];
    }
}