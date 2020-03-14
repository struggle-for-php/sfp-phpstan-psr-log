<?php

namespace SfpTest\PHPStan\Psr\Log;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\FileAnalyser;
use PHPStan\Analyser\IgnoredErrorHelper;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\Command\IgnoredRegexValidator;
use PHPStan\Command\IgnoredRegexValidatorResult;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\File\FileHelper;
use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\PhpDoc\PhpDocNodeResolver;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDoc\StubPhpDocProvider;
use PHPStan\Testing\TestCase;
use PHPStan\Type\FileTypeMapper;
use Psr\Log\LoggerTrait;

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
            __DIR__ . '/Asset/mislead_usage_of_context_exception.php'
        ];

        $files = \array_map([$this->getFileHelper(), 'normalizePath'], $files);

        $actualErrors = $analyser->analyse($files, true);

        $errors = '';
        foreach($actualErrors->getErrors() as $error) {
            $errors .= $error->getMessage() . "\n";
        }

$expects = <<<'EXPECT'
Parameter #2 $context of method SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl::emergency() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl::alert() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl::critical() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl::error() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl::warning() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl::notice() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl::info() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
Parameter #2 $context of method SfpTest\PHPStan\Psr\Log\Asset\LoggerImpl::debug() expects array()|array('exception' => Exception), array('exception' => 'foo') given.
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
        /** @var StubPhpDocProvider $stubPhpDocProvider */
        $stubPhpDocProvider = self::getContainer()->getService('stubPhpDocProvider');
        $med = $stubPhpDocProvider->findMethodPhpDoc(LoggerTrait::class, 'log', ['message']);


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
                    new AnonymousClassNameHelper(new FileHelper($currentWorkingDirectory),new FuzzyRelativePathHelper($currentWorkingDirectory, DIRECTORY_SEPARATOR, []))
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
                $fileHelper
            );
            $ignoredRegexValidator = $this->createMock(IgnoredRegexValidator::class);
            $ignoredRegexValidator->method('validate')
                ->willReturn(new IgnoredRegexValidatorResult([], false, false));
            $ignoredErrorHelper = new IgnoredErrorHelper(
                $ignoredRegexValidator,
                $fileHelper,
                [],
                true
            );
            $this->analyser = new Analyser(
                $fileAnalyser,
                $registry,
                $nodeScopeResolver,
                $ignoredErrorHelper,
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