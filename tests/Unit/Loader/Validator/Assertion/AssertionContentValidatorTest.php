<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Validator\Assertion;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Validator\Assertion\AssertionContentValidator;
use SmartAssert\Compiler\Loader\Validator\InvalidResult;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Loader\Validator\ValidResult;
use SmartAssert\Compiler\Tests\Unit\Loader\Validator\ValueDataProviderTrait;

class AssertionContentValidatorTest extends TestCase
{
    use ValueDataProviderTrait;

    private AssertionContentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = AssertionContentValidator::create();
    }

    #[DataProvider('invalidValueDataProvider')]
    public function testValidateNotValid(string $value, string $expectedReason): void
    {
        $expectedResult = new InvalidResult($value, ResultType::VALUE, $expectedReason);

        $this->assertEquals($expectedResult, $this->validator->validate($value));
    }

    #[DataProvider('validValueDataProvider')]
    #[DataProvider('validAssertionValueDataProvider')]
    public function testValidateIsValid(string $value): void
    {
        $expectedResult = new ValidResult($value);

        $this->assertEquals($expectedResult, $this->validator->validate($value));
    }

    /**
     * @return array<mixed>
     */
    public static function validAssertionValueDataProvider(): array
    {
        return [
            'descendant element dom identifier' => [
                'value' => '$"parent" >> $".selector"',
            ],
            'descendant attribute dom identifier' => [
                'value' => '$"parent" >> $".selector".attribute_name',
            ],
        ];
    }
}
