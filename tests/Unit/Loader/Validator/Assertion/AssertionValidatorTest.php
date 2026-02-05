<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Validator\Assertion;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Validator\Assertion\AssertionValidator;
use SmartAssert\Compiler\Loader\Validator\InvalidResult;
use SmartAssert\Compiler\Loader\Validator\InvalidResultInterface;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Loader\Validator\ValidResult;
use SmartAssert\Compiler\Loader\Validator\ValueValidator;
use webignition\BasilModels\Model\Statement\Assertion\Assertion;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;

class AssertionValidatorTest extends TestCase
{
    private AssertionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = AssertionValidator::create();
    }

    #[DataProvider('invalidAssertionDataProvider')]
    public function testValidateNotValid(AssertionInterface $assertion, InvalidResultInterface $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->validator->validate($assertion));
    }

    /**
     * @return array<mixed>
     */
    public static function invalidAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'invalid identifier' => [
                'assertion' => $assertionParser->parse('$elements.element_name is "value"', 0),
                'expectedResult' => new InvalidResult(
                    $assertionParser->parse('$elements.element_name is "value"', 0),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_IDENTIFIER,
                    new InvalidResult(
                        '$elements.element_name',
                        ResultType::VALUE,
                        ValueValidator::REASON_INVALID
                    )
                ),
            ],
            'invalid operator' => [
                'assertion' => new Assertion('$".button" glows', 0, '$".button"', 'glows'),
                'expectedResult' => (new InvalidResult(
                    new Assertion('$".button" glows', 0, '$".button"', 'glows'),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_OPERATOR
                ))->withContext([
                    AssertionValidator::CONTEXT_OPERATOR => 'glows',
                ]),
            ],
            'invalid value' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.element_name', 0),
                'expectedResult' => new InvalidResult(
                    $assertionParser->parse('$".selector" is $elements.element_name', 0),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_VALUE,
                    new InvalidResult(
                        '$elements.element_name',
                        ResultType::VALUE,
                        ValueValidator::REASON_INVALID
                    )
                ),
            ],
        ];
    }

    #[DataProvider('validAssertionIdentifierDataProvider')]
    #[DataProvider('validAssertionOperatorDataProvider')]
    #[DataProvider('validAssertionValueDataProvider')]
    public function testValidateIsValid(AssertionInterface $assertion): void
    {
        $expectedResult = new ValidResult($assertion);

        $this->assertEquals($expectedResult, $this->validator->validate($assertion));
    }

    /**
     * @return array<mixed>
     */
    public static function validAssertionIdentifierDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'identifier: element identifier' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"', 0),
            ],
            'identifier: descendant element identifier' => [
                'assertion' => $assertionParser->parse('$".parent" >> $".child" is "value"', 0),
            ],
            'identifier: attribute identifier' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"', 0),
            ],
            'identifier: quoted literal' => [
                'assertion' => $assertionParser->parse('"value" is "value"', 0),
            ],
            'identifier: browser property' => [
                'assertion' => $assertionParser->parse('$browser.size is "value"', 0),
            ],
            'identifier: page property' => [
                'assertion' => $assertionParser->parse('$page.title is "value"', 0),
            ],
            'identifier: data parameter' => [
                'assertion' => $assertionParser->parse('$data.key is "value"', 0),
            ],
            'identifier: environment parameter' => [
                'assertion' => $assertionParser->parse('$env.KEY is "value"', 0),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function validAssertionOperatorDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'operator: is' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"', 0),
            ],
            'operator: is-not' => [
                'assertion' => $assertionParser->parse('$".selector" is-not "value"', 0),
            ],
            'operator: exists' => [
                'assertion' => $assertionParser->parse('$".selector" exists', 0),
            ],
            'operator: not-exists' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists', 0),
            ],
            'operator: includes' => [
                'assertion' => $assertionParser->parse('$".selector" includes "value"', 0),
            ],
            'operator: excludes' => [
                'assertion' => $assertionParser->parse('$".selector" excludes "value"', 0),
            ],
            'operator: matches' => [
                'assertion' => $assertionParser->parse('$".selector" matches "value"', 0),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function validAssertionValueDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'value: element identifier' => [
                'assertion' => $assertionParser->parse('"value" is $".selector"', 0),
            ],
            'value: descendant element identifier' => [
                'assertion' => $assertionParser->parse('"value" is $".parent" >> $".child"', 0),
            ],
            'value: attribute identifier' => [
                'assertion' => $assertionParser->parse('"value" is $".selector".attribute_name', 0),
            ],
            'value: quoted literal' => [
                'assertion' => $assertionParser->parse('"value" is "value"', 0),
            ],
            'value: browser property' => [
                'assertion' => $assertionParser->parse('"value" is $browser.size', 0),
            ],
            'value: page property' => [
                'assertion' => $assertionParser->parse('"value" is $page.title', 0),
            ],
            'value: data parameter' => [
                'assertion' => $assertionParser->parse('"value" is $data.key', 0),
            ],
            'value: environment parameter' => [
                'assertion' => $assertionParser->parse('"value" is $env.KEY', 0),
            ],
        ];
    }
}
