<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Functional\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Command\GenerateCommand;
use webignition\BasilCliCompiler\Model\ExternalVariableIdentifiers;
use webignition\BasilCliCompiler\Services\CommandFactory;
use webignition\BasilCliCompiler\Services\CompiledClassResolver;
use webignition\BasilCliCompiler\Services\Compiler;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\AbstractEndToEndFailureTest;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;
use webignition\BasilModels\Step\Step;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\ObjectReflector\ObjectReflector;

class GenerateCommandFailureTest extends AbstractEndToEndFailureTest
{
    /**
     * @dataProvider unresolvedPlaceholderDataProvider
     *
     * @param array<mixed> $expectedErrorOutputData
     */
    public function testRunFailure(
        string $sourceRelativePath,
        int $expectedExitCode,
        string $expectedErrorOutputMessage,
        int $expectedErrorOutputCode,
        array $expectedErrorOutputData,
        ?callable $initializer = null
    ): void {
        $cliArguments = new CliArguments(
            $this->getRemoteSourcePrefix() . $sourceRelativePath,
            $this->getRemoteTarget(),
        );

        $compilationOutput = $this->getCompilationOutput($cliArguments, $initializer);
        self::assertSame($expectedExitCode, $compilationOutput->getExitCode());

        $output = $compilationOutput->getContent();

        $commandOutput = ErrorOutput::fromArray((array) Yaml::parse($output));
        $configuration = $commandOutput->getConfiguration();
        self::assertSame($cliArguments->getSource(), $configuration->getSource());
        self::assertSame($cliArguments->getTarget(), $configuration->getTarget());
        self::assertSame(AbstractBaseTest::class, $configuration->getBaseClass());

        $expectedErrorOutputData = $this->replaceConfigurationPlaceholders($expectedErrorOutputData);

        $expectedCommandOutput = new ErrorOutput(
            new Configuration(
                $cliArguments->getSource(),
                $cliArguments->getTarget(),
                AbstractBaseTest::class
            ),
            $this->replaceConfigurationPlaceholdersInString($expectedErrorOutputMessage),
            $expectedErrorOutputCode,
            $expectedErrorOutputData
        );

        self::assertEquals($expectedCommandOutput, $commandOutput);
    }

    /**
     * @return array<mixed>
     */
    public function unresolvedPlaceholderDataProvider(): array
    {
        return [
            'placeholder CLIENT is not defined' => [
                'sourceRelativePath' => '/Test/example.com.verify-open-literal.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_GENERATOR_UNRESOLVED_PLACEHOLDER,
                'expectedErrorOutputMessage' => 'Unresolved variable "CLIENT" in template ' .
                    '"{{ CLIENT }}->request(\'GET\', \'https://example.com/\');"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_GENERATOR_UNRESOLVED_PLACEHOLDER,
                'expectedErrorOutputData' => [
                    'placeholder' => 'CLIENT',
                    'content' => '{{ CLIENT }}->request(\'GET\', \'https://example.com/\');',
                ],
                'initializer' => function (GenerateCommand $command) {
                    $mockExternalVariableIdentifiers = \Mockery::mock(ExternalVariableIdentifiers::class);
                    $mockExternalVariableIdentifiers
                        ->shouldReceive('get')
                        ->andReturn([])
                    ;

                    $this->mockCompilerCompiledClassResolverExternalVariableIdentifiers(
                        $command,
                        $mockExternalVariableIdentifiers
                    );
                }
            ],
        ];
    }

    /**
     * @dataProvider runFailureUnsupportedStepDataProvider
     *
     * @param array<mixed> $expectedErrorOutputContext
     */
    public function testRunFailureUnsupportedStepException(
        UnsupportedStepException $unsupportedStepException,
        array $expectedErrorOutputContext
    ): void {
        $root = getcwd();

        $compilationOutput = $this->getCompilationOutput(
            new CliArguments(
                $root . '/tests/Fixtures/basil/Test/example.com.verify-open-literal.yml',
                $root . '/tests/build/target'
            ),
            function (GenerateCommand $command) use ($unsupportedStepException) {
                $compiler = \Mockery::mock(Compiler::class);
                $compiler
                    ->shouldReceive('compile')
                    ->andThrow($unsupportedStepException)
                ;

                ObjectReflector::setProperty(
                    $command,
                    GenerateCommand::class,
                    'compiler',
                    $compiler
                );
            }
        );

        self::assertSame(ErrorOutputFactory::CODE_GENERATOR_UNSUPPORTED_STEP, $compilationOutput->getExitCode());

        $expectedCommandOutput = new ErrorOutput(
            new Configuration(
                $root . '/tests/Fixtures/basil/Test/example.com.verify-open-literal.yml',
                $root . '/tests/build/target',
                AbstractBaseTest::class
            ),
            'Unsupported step',
            ErrorOutputFactory::CODE_GENERATOR_UNSUPPORTED_STEP,
            $expectedErrorOutputContext
        );

        $commandOutput = ErrorOutput::fromArray((array) Yaml::parse($compilationOutput->getContent()));

        self::assertEquals($expectedCommandOutput, $commandOutput);
    }

    /**
     * @return array<mixed>
     */
    public function runFailureUnsupportedStepDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'click action with attribute identifier' => [
                'unsupportedStepException' => new UnsupportedStepException(
                    new Step(
                        [
                            $actionParser->parse('click $".selector".attribute_name'),
                        ],
                        []
                    ),
                    new UnsupportedStatementException(
                        $actionParser->parse('click $".selector".attribute_name'),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_IDENTIFIER,
                            '$".selector".attribute_name'
                        )
                    )
                ),
                'expectedErrorOutputContext' => [
                    'statement_type' => 'action',
                    'statement' => 'click $".selector".attribute_name',
                    'content_type' => 'identifier',
                    'content' => '$".selector".attribute_name',
                ],
            ],
            'comparison assertion examined value identifier cannot be extracted' => [
                'unsupportedStepException' => new UnsupportedStepException(
                    new Step(
                        [],
                        [
                            $assertionParser->parse('$".selector" is "value"'),
                        ]
                    ),
                    new UnsupportedStatementException(
                        $assertionParser->parse('$".selector" is "value"'),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_IDENTIFIER,
                            '$".selector"'
                        )
                    )
                ),
                'expectedErrorOutputContext' => [
                    'statement_type' => 'assertion',
                    'statement' => '$".selector" is "value"',
                    'content_type' => 'identifier',
                    'content' => '$".selector"',
                ],
            ],
            'comparison assertion examined value is not supported' => [
                'unsupportedStepException' => new UnsupportedStepException(
                    new Step(
                        [],
                        [
                            $assertionParser->parse('$elements.element_name is "value"'),
                        ]
                    ),
                    new UnsupportedStatementException(
                        $assertionParser->parse('$elements.element_name is "value"'),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_VALUE,
                            '$elements.element_name'
                        )
                    )
                ),
                'expectedErrorOutputContext' => [
                    'statement_type' => 'assertion',
                    'statement' => '$elements.element_name is "value"',
                    'content_type' => 'value',
                    'content' => '$elements.element_name',
                ],
            ],
            'unsupported action type' => [
                'unsupportedStepException' => new UnsupportedStepException(
                    new Step(
                        [
                            $actionParser->parse('foo $".selector"'),
                        ],
                        []
                    ),
                    new UnsupportedStatementException(
                        $actionParser->parse('foo $".selector"')
                    )
                ),
                'expectedErrorOutputContext' => [
                    'statement_type' => 'action',
                    'statement' => 'foo $".selector"',
                ],
            ],
        ];
    }

    protected function getRemoteSourcePrefix(): string
    {
        return getcwd() . '/tests/Fixtures/basil';
    }

    protected function getRemoteTarget(): string
    {
        return getcwd() . '/tests/build/target';
    }

    protected function getCompilationOutput(
        CliArguments $cliArguments,
        ?callable $initializer = null
    ): CompilationOutput {
        $stdout = new BufferedOutput();
        $stderr = new BufferedOutput();
        $command = CommandFactory::createGenerateCommand($stdout, $stderr, $cliArguments->toArgvArray());

        if (null !== $initializer) {
            $initializer($command);
        }

        $exitCode = $command->run(new ArrayInput($cliArguments->getOptions()), $stderr);

        return new CompilationOutput($stderr->fetch(), $exitCode);
    }

    private function mockCompilerCompiledClassResolverExternalVariableIdentifiers(
        GenerateCommand $command,
        ExternalVariableIdentifiers $updatedExternalVariableIdentifiers
    ): void {
        $compiledClassResolver = CompiledClassResolver::createResolver($updatedExternalVariableIdentifiers);
        $compiler = ObjectReflector::getProperty($command, 'compiler');

        ObjectReflector::setProperty(
            $compiler,
            Compiler::class,
            'compiledClassResolver',
            $compiledClassResolver
        );

        ObjectReflector::setProperty(
            $command,
            GenerateCommand::class,
            'compiler',
            $compiler
        );
    }
}
