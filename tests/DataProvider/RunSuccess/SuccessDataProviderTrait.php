<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunSuccess;

use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\ExpectedGeneratedTest;
use webignition\BasilCliCompiler\Tests\Model\ExpectedGeneratedTestCollection;

trait SuccessDataProviderTrait
{
    /**
     * @return array[]
     */
    public function successDataProvider(): array
    {
        return [
            'single test' => [
                'cliArguments' => new CliArguments(
                    FixturePaths::getTest() . '/example.com.verify-open-literal.yml',
                    FixturePaths::getTarget()
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
