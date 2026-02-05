<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\DataSetLoader;
use SmartAssert\Compiler\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;

class DataSetLoaderTest extends TestCase
{
    #[DataProvider('loadDataProvider')]
    public function testLoad(string $path, DataSetCollectionInterface $expectedDataSetCollection): void
    {
        $dataSetLoader = DataSetLoader::createLoader();

        $dataSetCollection = $dataSetLoader->load($path);

        $this->assertEquals($expectedDataSetCollection, $dataSetCollection);
    }

    /**
     * @return array<mixed>
     */
    public static function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('basil/Empty/empty.yml'),
                'expectedDataSetCollection' => new DataSetCollection([]),
            ],
            'non-empty, expected title only' => [
                'path' => FixturePathFinder::find('basil/DataProvider/expected-title-only.yml'),
                'expectedDataSetCollection' => new DataSetCollection([
                    '0' => [
                        'expected_title' => 'Foo',
                    ],
                    '1' => [
                        'expected_title' => 'Bar',
                    ],
                ]),
            ],
            'non-empty, users' => [
                'path' => FixturePathFinder::find('basil/DataProvider/users.yml'),
                'expectedDataSetCollection' => new DataSetCollection([
                    'user1' => [
                        'username' => 'user1',
                        'role' => 'user',
                    ],
                    'user2' => [
                        'username' => 'user2',
                        'role' => 'admin',
                    ],
                ]),
            ],
        ];
    }
}
