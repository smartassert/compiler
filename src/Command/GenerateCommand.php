<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Command;

use SmartAssert\Compiler\Model\Options;
use SmartAssert\Compiler\Services\Compiler;
use SmartAssert\Compiler\Services\ErrorOutputFactory;
use SmartAssert\Compiler\Services\OutputRenderer;
use SmartAssert\Compiler\Services\TestWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface as ConsoleOutputInterface;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilerModels\ErrorOutput;
use webignition\BasilCompilerModels\TestManifest;
use webignition\BasilCompilerModels\TestManifestCollection;
use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\Resolver\CircularStepImportException;
use webignition\BasilLoader\Resolver\UnknownElementException;
use webignition\BasilLoader\Resolver\UnknownPageElementException;
use webignition\BasilLoader\TestLoader;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\Stubble\UnresolvedVariableException;

class GenerateCommand extends Command
{
    private const NAME = 'generate';

    public function __construct(
        private TestLoader $testLoader,
        private Compiler $compiler,
        private TestWriter $testWriter,
        private ErrorOutputFactory $errorOutputFactory,
        private OutputRenderer $outputRenderer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate tests from basil source')
            ->addOption(
                Options::OPTION_SOURCE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the basil test source from which to generate tests. ' .
                'Can be absolute or relative to this directory.',
                ''
            )
            ->addOption(
                Options::OPTION_TARGET,
                null,
                InputOption::VALUE_REQUIRED,
                'Output path for generated tests',
                ''
            )
            ->addOption(
                Options::OPTION_BASE_CLASS,
                null,
                InputOption::VALUE_OPTIONAL,
                'Base class to extend',
                AbstractBaseTest::class
            )
        ;
    }

    protected function execute(InputInterface $input, ConsoleOutputInterface $output): int
    {
        $source = $input->getOption(Options::OPTION_SOURCE);
        $source = is_string($source) ? trim($source) : '';

        if ('' === $source) {
            return $this->outputRenderer->render(new ErrorOutput(
                'source empty; call with --source=SOURCE',
                ErrorOutputFactory::CODE_COMMAND_CONFIG_SOURCE_EMPTY
            ));
        }

        if (!str_starts_with($source, '/')) {
            return $this->outputRenderer->render(new ErrorOutput(
                'source invalid: path must be absolute',
                ErrorOutputFactory::CODE_COMMAND_CONFIG_SOURCE_NOT_ABSOLUTE
            ));
        }

        if (!is_readable($source)) {
            return $this->outputRenderer->render(new ErrorOutput(
                'source invalid; file is not readable',
                ErrorOutputFactory::CODE_COMMAND_CONFIG_SOURCE_NOT_READABLE
            ));
        }

        $target = $input->getOption(Options::OPTION_TARGET);
        $target = is_string($target) ? trim($target) : '';

        if ('' === $target) {
            return $this->outputRenderer->render(new ErrorOutput(
                'target empty; call with --target=TARGET',
                ErrorOutputFactory::CODE_COMMAND_CONFIG_TARGET_EMPTY
            ));
        }

        if (!str_starts_with($target, '/')) {
            return $this->outputRenderer->render(new ErrorOutput(
                'target invalid: path must be absolute',
                ErrorOutputFactory::CODE_COMMAND_CONFIG_TARGET_NOT_ABSOLUTE
            ));
        }

        if (!is_dir($target)) {
            return $this->outputRenderer->render(new ErrorOutput(
                'target invalid; is not a directory (is it a file?)',
                ErrorOutputFactory::CODE_COMMAND_CONFIG_TARGET_NOT_A_DIRECTORY
            ));
        }

        if (!is_writable($target)) {
            return $this->outputRenderer->render(new ErrorOutput(
                'target invalid; directory is not writable',
                ErrorOutputFactory::CODE_COMMAND_CONFIG_TARGET_NOT_WRITABLE
            ));
        }

        $baseClass = $input->getOption(Options::OPTION_BASE_CLASS);
        $baseClass = is_string($baseClass) ? trim($baseClass) : '';

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
                    $test->getConfiguration()->getBrowser(),
                    $test->getConfiguration()->getUrl(),
                    $test->getPath() ?? '',
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
