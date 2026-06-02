<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Command;

use SmartAssert\Compiler\ExitCode;
use SmartAssert\Compiler\Loader\Exception\EmptyTestException;
use SmartAssert\Compiler\Loader\Exception\InvalidPageException;
use SmartAssert\Compiler\Loader\Exception\InvalidTestException;
use SmartAssert\Compiler\Loader\Exception\NonRetrievableImportException;
use SmartAssert\Compiler\Loader\Exception\ParseException;
use SmartAssert\Compiler\Loader\Exception\YamlLoaderException;
use SmartAssert\Compiler\Loader\Resolver\CircularStepImportException;
use SmartAssert\Compiler\Loader\Resolver\UnknownElementException;
use SmartAssert\Compiler\Loader\Resolver\UnknownPageElementException;
use SmartAssert\Compiler\Loader\TestLoader;
use SmartAssert\Compiler\Model\Options;
use SmartAssert\Compiler\Services\Compiler;
use SmartAssert\Compiler\Services\ErrorOutputFactory;
use SmartAssert\Compiler\Services\OutputRenderer;
use SmartAssert\Compiler\Services\TestWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilerModels\Model\ErrorOutput;
use webignition\BasilCompilerModels\Model\TestManifest;
use webignition\BasilCompilerModels\Model\TestManifestCollection;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\Stubble\UnresolvedVariableException;

#[AsCommand(name: 'generate')]
class GenerateCommand extends Command
{
    public function __construct(
        private TestLoader $testLoader,
        private Compiler $compiler,
        private TestWriter $testWriter,
        private ErrorOutputFactory $errorOutputFactory,
        private OutputRenderer $outputRenderer,
    ) {
        parent::__construct();
    }

    public function __invoke(
        #[Option(name: Options::OPTION_SOURCE)]
        string $source = '',
        #[Option(name: Options::OPTION_TARGET)]
        string $target = '',
        #[Option(name: Options::OPTION_BASE_CLASS)]
        string $baseClass = AbstractBaseTest::class,
    ): int {
        if ('' === $source) {
            return $this->outputRenderer->render(new ErrorOutput(
                'source empty; call with --source=SOURCE',
                ExitCode::CONFIG_SOURCE_EMPTY->value
            ));
        }

        if (!str_starts_with($source, '/')) {
            return $this->outputRenderer->render(new ErrorOutput(
                'source invalid: path must be absolute',
                ExitCode::CONFIG_SOURCE_NOT_ABSOLUTE->value
            ));
        }

        if (!is_readable($source)) {
            return $this->outputRenderer->render(new ErrorOutput(
                'source invalid; file is not readable',
                ExitCode::CONFIG_SOURCE_NOT_READABLE->value
            ));
        }

        if ('' === $target) {
            return $this->outputRenderer->render(new ErrorOutput(
                'target empty; call with --target=TARGET',
                ExitCode::CONFIG_TARGET_EMPTY->value
            ));
        }

        if (!str_starts_with($target, '/')) {
            return $this->outputRenderer->render(new ErrorOutput(
                'target invalid: path must be absolute',
                ExitCode::CONFIG_TARGET_NOT_ABSOLUTE->value
            ));
        }

        if (!is_dir($target)) {
            return $this->outputRenderer->render(new ErrorOutput(
                'target invalid; is not a directory (is it a file?)',
                ExitCode::CONFIG_TARGET_NOT_A_DIRECTORY->value
            ));
        }

        if (!is_writable($target)) {
            return $this->outputRenderer->render(new ErrorOutput(
                'target invalid; directory is not writable',
                ExitCode::CONFIG_TARGET_NOT_WRITABLE->value
            ));
        }

        if ('' === $baseClass) {
            return $this->outputRenderer->render(new ErrorOutput(
                'base class empty',
                ExitCode::CONFIG_BASE_CLASS_EMPTY->value
            ));
        }

        $testManifests = [];

        try {
            $tests = $this->testLoader->load($source);
        } catch (
            CircularStepImportException |
            EmptyTestException |
            InvalidPageException |
            InvalidTestException |
            NonRetrievableImportException |
            ParseException |
            UnknownElementException |
            UnknownItemException |
            UnknownPageElementException |
            YamlLoaderException $exception
        ) {
            $errorOutput = $this->errorOutputFactory->createForException($exception);

            $this->outputRenderer->render($errorOutput);

            return $errorOutput->getCode();
        }

        try {
            foreach ($tests as $test) {
                $compiledTest = $this->compiler->compile($test, $baseClass);
                $writtenTarget = $this->testWriter->write($compiledTest, $target);

                $testManifests[] = new TestManifest(
                    $test->getBrowser(),
                    $test->getUrl(),
                    $test->getName(),
                    $writtenTarget,
                    $test->getSteps()->getStepNames()
                );
            }
        } catch (
            UnresolvedVariableException |
            UnsupportedStepException $exception
        ) {
            $errorOutput = $this->errorOutputFactory->createForException($exception);

            $this->outputRenderer->render($errorOutput);

            return $errorOutput->getCode();
        }

        return $this->outputRenderer->render(new TestManifestCollection($testManifests));
    }
}
