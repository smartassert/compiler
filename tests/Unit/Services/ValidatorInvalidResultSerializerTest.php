<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\Compiler\Services\ValidatorInvalidResultSerializer;
use SmartAssert\Compiler\Tests\Unit\AbstractBaseTestCase;
use SmartAssert\Compiler\Loader\Validator\Action\ActionValidator;
use SmartAssert\Compiler\Loader\Validator\Assertion\AssertionValidator;
use SmartAssert\Compiler\Loader\Validator\InvalidResultInterface;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Loader\Validator\Step\StepValidator;
use SmartAssert\Compiler\Loader\Validator\Test\TestValidator;
use SmartAssert\Compiler\Loader\Validator\ValueValidator;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilModels\Parser\StepParser;
use webignition\BasilModels\Parser\Test\TestParser;

class ValidatorInvalidResultSerializerTest extends AbstractBaseTestCase
{
    private ValidatorInvalidResultSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = new ValidatorInvalidResultSerializer();
    }

    /**
     * @param array<mixed> $expectedData
     */
    #[DataProvider('serializeToArrayDataProvider')]
    public function testSerializeToArray(InvalidResultInterface $invalidResult, array $expectedData): void
    {
        self::assertSame($expectedData, $this->serializer->serializeToArray($invalidResult));
    }

    /**
     * @return array<mixed>
     */
    public static function serializeToArrayDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $actionValidator = ActionValidator::create();
        $assertionParser = AssertionParser::create();
        $assertionValidator = AssertionValidator::create();
        $stepParser = StepParser::create();
        $stepValidator = StepValidator::create();
        $testParser = TestParser::create();
        $testValidator = TestValidator::create();

        $actionWithInvalidIdentifier = $actionParser->parse('click $".selector".attribute_name', 0);
        $actionWithInvalidIdentifierResult = $actionValidator->validate($actionWithInvalidIdentifier);

        $actionWithInvalidValue = $actionParser->parse('set $".selector" to $page.invalid', 0);
        $actionWithInvalidValueResult = $actionValidator->validate($actionWithInvalidValue);

        $assertionWithInvalidComparison = $assertionParser->parse('$".button" glows', 0);
        $assertionWithInvalidComparisonResult = $assertionValidator->validate($assertionWithInvalidComparison);

        $stepWithInvalidAction = $stepParser->parse([
            'actions' => [
                (string) $actionWithInvalidValue,
            ],
            'assertions' => [
                '$page.url is "http://example.com"',
            ],
        ]);
        $stepWithInvalidActionResult = $stepValidator->validate($stepWithInvalidAction);

        $testWithStpWithInvalidAction = $testParser->parse([
            'config' => [
                'url' => 'http://example.com',
                'browser' => 'chrome',
            ],
            'invalid step name' => [
                'actions' => [
                    (string) $actionWithInvalidValue,
                ],
                'assertions' => [
                    '$page.url is "http://example.com"',
                ],
            ],
        ]);
        $testWithStpWithInvalidActionResult = $testValidator->validate($testWithStpWithInvalidAction);

        return [
            'action, no context, no previous' => [
                'invalidResult' => $actionWithInvalidIdentifierResult,
                'expectedData' => [
                    'type' => ResultType::ACTION,
                    'reason' => ActionValidator::REASON_INVALID_IDENTIFIER,
                    'subject' => '"click $\".selector\".attribute_name"',
                ],
            ],
            'action, has previous' => [
                'invalidResult' => $actionWithInvalidValueResult,
                'expectedData' => [
                    'type' => ResultType::ACTION,
                    'reason' => ActionValidator::REASON_INVALID_VALUE,
                    'subject' => '"set $\".selector\" to $page.invalid"',
                    'previous' => [
                        'type' => ResultType::VALUE,
                        'reason' => ValueValidator::REASON_PROPERTY_INVALID,
                        'subject' => '"$page.invalid"',
                    ],
                ],
            ],
            'assertion, has context' => [
                'invalidResult' => $assertionWithInvalidComparisonResult,
                'expectedData' => [
                    'type' => ResultType::ASSERTION,
                    'reason' => AssertionValidator::REASON_INVALID_OPERATOR,
                    'context' => [
                        'operator' => 'glows',
                    ],
                    'subject' => '"$\".button\" glows"',
                ],
            ],
            'step with invalid action' => [
                'invalidResult' => $stepWithInvalidActionResult,
                'expectedData' => [
                    'type' => ResultType::STEP,
                    'reason' => StepValidator::REASON_INVALID_ACTION,
                    'previous' => [
                        'type' => ResultType::ACTION,
                        'reason' => ActionValidator::REASON_INVALID_VALUE,
                        'subject' => '"set $\".selector\" to $page.invalid"',
                        'previous' => [
                            'type' => ResultType::VALUE,
                            'reason' => ValueValidator::REASON_PROPERTY_INVALID,
                            'subject' => '"$page.invalid"',
                        ],
                    ],
                ],
            ],
            'test with step with invalid action' => [
                'invalidResult' => $testWithStpWithInvalidActionResult,
                'expectedData' => [
                    'type' => ResultType::TEST,
                    'reason' => TestValidator::REASON_STEP_INVALID,
                    'context' => [
                        'step-name' => 'invalid step name',
                    ],
                    'previous' => [
                        'type' => ResultType::STEP,
                        'reason' => StepValidator::REASON_INVALID_ACTION,
                        'previous' => [
                            'type' => ResultType::ACTION,
                            'reason' => ActionValidator::REASON_INVALID_VALUE,
                            'subject' => '"set $\".selector\" to $page.invalid"',
                            'previous' => [
                                'type' => ResultType::VALUE,
                                'reason' => ValueValidator::REASON_PROPERTY_INVALID,
                                'subject' => '"$page.invalid"',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
