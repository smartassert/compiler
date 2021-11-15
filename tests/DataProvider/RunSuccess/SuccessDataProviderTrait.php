<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunSuccess;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\ExpectedGeneratedTest;
use webignition\BasilCliCompiler\Tests\Model\ExpectedGeneratedTestCollection;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\SuiteManifest;
use webignition\BasilCompilerModels\TestManifest;
use webignition\BasilModels\Test\Configuration as TestModelConfiguration;

trait SuccessDataProviderTrait
{
    /**
     * @return array[]
     */
    public function successDataProvider(): array
    {
        $root = getcwd();

        return [
            'single test' => [
                'cliArguments' => new CliArguments(
                    FixturePaths::getTest() . '/example.com.verify-open-literal.yml',
                    FixturePaths::getTarget()
                ),
                'expectedExitCode' => 0,
                'expectedOutput' => new SuiteManifest(
                    new Configuration(
                        FixturePaths::getTest() . '/example.com.verify-open-literal.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    [
                        new TestManifest(
                            new TestModelConfiguration('chrome', 'https://example.com/'),
                            FixturePaths::getTest() . '/example.com.verify-open-literal.yml',
                            $root . '/tests/build/target/GeneratedVerifyOpenLiteralChrome.php',
                            1
                        ),
                    ]
                ),
                'expectedGeneratedTests' => new ExpectedGeneratedTestCollection([
                    new ExpectedGeneratedTest(
                        'GeneratedVerifyOpenLiteralChrome',
                        '/tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php',
                    ),
                ]),
            ],
            'single test, verify open literal with page import' => [
                'cliArguments' => new CliArguments(
                    FixturePaths::getTest() . '/example.com.import-page.yml',
                    FixturePaths::getTarget()
                ),
                'expectedExitCode' => 0,
                'expectedOutput' => new SuiteManifest(
                    new Configuration(
                        FixturePaths::getTest() . '/example.com.import-page.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    [
                        new TestManifest(
                            new TestModelConfiguration('chrome', 'http://example.com'),
                            FixturePaths::getTest() . '/example.com.import-page.yml',
                            FixturePaths::getTarget() . '/GeneratedImportPage.php',
                            1
                        ),
                    ]
                ),
                'expectedGeneratedTests' => new ExpectedGeneratedTestCollection([
                    new ExpectedGeneratedTest(
                        'GeneratedImportPage',
                        '/tests/Fixtures/php/Test/GeneratedImportPage.php',
                    ),
                ]),
            ],
            'single test with multiple browsers' => [
                'cliArguments' => new CliArguments(
                    FixturePaths::getTest() . '/example.com.verify-open-literal-multiple-browsers.yml',
                    FixturePaths::getTarget()
                ),
                'expectedExitCode' => 0,
                'expectedOutput' => new SuiteManifest(
                    new Configuration(
                        FixturePaths::getTest() . '/example.com.verify-open-literal-multiple-browsers.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    [
                        new TestManifest(
                            new TestModelConfiguration('chrome', 'https://example.com/'),
                            FixturePaths::getTest() . '/example.com.verify-open-literal-multiple-browsers.yml',
                            $root . '/tests/build/target/GeneratedVerifyOpenLiteralChrome.php',
                            1
                        ),
                        new TestManifest(
                            new TestModelConfiguration('firefox', 'https://example.com/'),
                            FixturePaths::getTest() . '/example.com.verify-open-literal-multiple-browsers.yml',
                            $root . '/tests/build/target/GeneratedVerifyOpenLiteralFirefox.php',
                            1
                        ),
                    ]
                ),
                'expectedGeneratedTests' => new ExpectedGeneratedTestCollection([
                    new ExpectedGeneratedTest(
                        'GeneratedVerifyOpenLiteralChrome',
                        '/tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php',
                    ),
                    new ExpectedGeneratedTest(
                        'GeneratedVerifyOpenLiteralFirefox',
                        '/tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralFirefox.php',
                    ),
                ]),
            ],
        ];
    }
}
