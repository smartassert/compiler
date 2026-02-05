<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Validator\InvalidResult;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Loader\Validator\ValidResult;
use SmartAssert\Compiler\Loader\Validator\ValueValidator;

class ValueValidatorTest extends TestCase
{
    use ValueDataProviderTrait;

    private ValueValidator $valueValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->valueValidator = ValueValidator::create();
    }

    #[DataProvider('invalidValueDataProvider')]
    public function testValidateNotValid(string $value, string $expectedReason): void
    {
        $expectedResult = new InvalidResult($value, ResultType::VALUE, $expectedReason);

        $this->assertEquals($expectedResult, $this->valueValidator->validate($value));
    }

    #[DataProvider('validValueDataProvider')]
    public function testValidateIsValid(string $value): void
    {
        $expectedResult = new ValidResult($value);

        $this->assertEquals($expectedResult, $this->valueValidator->validate($value));
    }
}
