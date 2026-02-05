<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Validator\LiteralValueIdentifier;

class LiteralValueIdentifierTest extends TestCase
{
    private LiteralValueIdentifier $literalValueIdentifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->literalValueIdentifier = new LiteralValueIdentifier();
    }

    #[DataProvider('isDataProvider')]
    public function testIs(string $value, bool $expectedIs): void
    {
        $this->assertSame($this->literalValueIdentifier->is($value), $expectedIs);
    }

    /**
     * @return array<mixed>
     */
    public static function isDataProvider(): array
    {
        return [
            'empty' => [
                'value' => '',
                'expectedIs' => false,
            ],
            'whitespace' => [
                'value' => '   ',
                'expectedIs' => false,
            ],
            'unquoted' => [
                'value' => 'value',
                'expectedIs' => false,
            ],
            'no ending quote' => [
                'value' => '"value',
                'expectedIs' => false,
            ],
            'no starting quote' => [
                'value' => 'value"',
                'expectedIs' => false,
            ],
            'no ending quote; has escaped quotes' => [
                'value' => '"va\"lu\"e\"',
                'expectedIs' => false,
            ],
            'no starting quote; has escaped quotes' => [
                'value' => '\"va\"lu\"e\""',
                'expectedIs' => false,
            ],
            'quoted' => [
                'value' => '"value"',
                'expectedIs' => true,
            ],
            'quoted; has escaped quotes' => [
                'value' => '"va\"lu\"e"',
                'expectedIs' => true,
            ],
        ];
    }
}
