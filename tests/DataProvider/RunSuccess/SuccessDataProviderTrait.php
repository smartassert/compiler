<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunSuccess;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
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
                'input' => [
                    '--source' => FixturePaths::getTest() . '/example.com.verify-open-literal.yml',
                    '--target' => FixturePaths::getTarget(),
                ],
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
                'expectedGeneratedCodePaths' => [
                    'tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php',
                ],
                'classNames' => [
                    'GeneratedVerifyOpenLiteralChrome',
                ],
            ],
            'single test, verify open literal with page import' => [
                'input' => [
                    '--source' => FixturePaths::getTest() . '/example.com.import-page.yml',
                    '--target' => FixturePaths::getTarget(),
                ],
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
                'expectedGeneratedCodePaths' => [
                    'tests/Fixtures/php/Test/GeneratedImportPage.php',
                ],
                'classNames' => [
                    'GeneratedImportPage',
                ],
            ],
            'single test with multiple browsers' => [
                'input' => [
                    '--source' => FixturePaths::getTest() . '/example.com.verify-open-literal-multiple-browsers.yml',
                    '--target' => FixturePaths::getTarget(),
                ],
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
                'expectedGeneratedCodePaths' => [
                    'tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php',
                    'tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralFirefox.php',
                ],
                'classNames' => [
                    'GeneratedVerifyOpenLiteralChrome',
                    'GeneratedVerifyOpenLiteralFirefox',
                ],
            ],
        ];
    }
}
