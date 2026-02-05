<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\StepLoader;
use SmartAssert\Compiler\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Model\Statement\Action\ActionCollection;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollection;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;

class StepLoaderTest extends TestCase
{
    #[DataProvider('loadDataProvider')]
    public function testLoad(string $path, StepInterface $expectedStep): void
    {
        $stepLoader = StepLoader::createLoader();

        $step = $stepLoader->load($path);

        $this->assertEquals($expectedStep, $step);
    }

    /**
     * @return array<mixed>
     */
    public static function loadDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'empty' => [
                'path' => FixturePathFinder::find('basil/Empty/empty.yml'),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([])),
            ],
            'literal' => [
                'path' => FixturePathFinder::find('basil/Step/no-parameters.yml'),
                'expectedStep' => new Step(
                    new ActionCollection([
                        $actionParser->parse('click $".button"', 0),
                    ]),
                    new AssertionCollection([
                        $assertionParser->parse('$".heading" includes "example"', 1),
                    ])
                ),
            ],
            'deferred import' => [
                'path' => FixturePathFinder::find('basil/Step/deferred_import.yml'),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('no_parameters_import_name'),
            ],
        ];
    }
}
