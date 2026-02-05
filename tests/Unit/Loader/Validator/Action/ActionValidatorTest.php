<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Validator\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Validator\Action\ActionValidator;
use SmartAssert\Compiler\Loader\Validator\InvalidResult;
use SmartAssert\Compiler\Loader\Validator\InvalidResultInterface;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Loader\Validator\ValidResult;
use SmartAssert\Compiler\Loader\Validator\ValueValidator;
use webignition\BasilModels\Model\Statement\Action\Action;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Parser\ActionParser;

class ActionValidatorTest extends TestCase
{
    private ActionValidator $actionValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actionValidator = ActionValidator::create();
    }

    #[DataProvider('validateIsValidDataProvider')]
    public function testValidateIsValid(ActionInterface $action): void
    {
        $this->assertEquals(new ValidResult($action), $this->actionValidator->validate($action));
    }

    /**
     * @return array<mixed>
     */
    public static function validateIsValidDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'click element identifier' => [
                'action' => $actionParser->parse('click $".selector"', 0),
            ],
            'click descendant dom identifier' => [
                'action' => $actionParser->parse('click $".parent" >> $".child"', 0),
            ],
            'click single-character CSS selector element identifier' => [
                'action' => $actionParser->parse('click $"a"', 0),
            ],
            'submit element identifier' => [
                'action' => $actionParser->parse('submit $".selector"', 0),
            ],
            'wait-for element identifier' => [
                'action' => $actionParser->parse('wait-for $".selector"', 0),
            ],
            'wait-for descendant dom identifier' => [
                'action' => $actionParser->parse('wait-for $".parent" >> $".child"', 0),
            ],
            'wait literal value (unquoted)' => [
                'action' => $actionParser->parse('wait 1', 0),
            ],
            'wait literal value (quoted)' => [
                'action' => $actionParser->parse('wait "1"', 0),
            ],
            'wait element identifier value' => [
                'action' => $actionParser->parse('wait $".selector"', 0),
            ],
            'wait descendant dom identifier' => [
                'action' => $actionParser->parse('wait $".parent" >> $".child"', 0),
            ],
            'wait attribute identifier value' => [
                'action' => $actionParser->parse('wait $".selector".attribute', 0),
            ],
            'wait browser size value' => [
                'action' => $actionParser->parse('wait $browser.size', 0),
            ],
            'wait page title value' => [
                'action' => $actionParser->parse('wait $page.title', 0),
            ],
            'wait page url value' => [
                'action' => $actionParser->parse('wait $page.url', 0),
            ],
            'wait data parameter value' => [
                'action' => $actionParser->parse('wait $data.key', 0),
            ],
            'wait environment parameter value' => [
                'action' => $actionParser->parse('wait $env.KEY', 0),
            ],
            'set; literal value' => [
                'action' => $actionParser->parse('set $".selector" to "value"', 0),
            ],
            'set; element identifier value' => [
                'action' => $actionParser->parse('set $".selector" to $".value"', 0),
            ],
            'set; descendant dom identifier value' => [
                'action' => $actionParser->parse('set $".selector" to $".parent" >> $".child"', 0),
            ],
            'set; descendant dom identifier identifier and value' => [
                'action' => $actionParser->parse('set $".parent" >> $".child" to $".parent" >> $".child"', 0),
            ],
            'set; attribute identifier value' => [
                'action' => $actionParser->parse('set $".selector" to $".element".attribute', 0),
            ],
            'set; browser size value' => [
                'action' => $actionParser->parse('set $".selector" to $browser.size', 0),
            ],
            'set; page title value' => [
                'action' => $actionParser->parse('set $".selector" to $page.title', 0),
            ],
            'set; page url value' => [
                'action' => $actionParser->parse('set $".selector" to $page.url', 0),
            ],
            'set; data parameter value' => [
                'action' => $actionParser->parse('set $".selector" to $data.key', 0),
            ],
            'set; environment parameter value' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY', 0),
            ],
            'reload, no args' => [
                'action' => $actionParser->parse('reload', 0),
            ],
            'back, no args' => [
                'action' => $actionParser->parse('back', 0),
            ],
            'forward, no args' => [
                'action' => $actionParser->parse('forward', 0),
            ],
            'reload, with args' => [
                'action' => $actionParser->parse('reload arg1 arg2', 0),
            ],
            'back, with args' => [
                'action' => $actionParser->parse('back arg1 arg2', 0),
            ],
            'forward, with args' => [
                'action' => $actionParser->parse('forward arg1 arg2', 0),
            ],
        ];
    }

    #[DataProvider('invalidInteractionActionDataProvider')]
    #[DataProvider('invalidInputActionDataProvider')]
    #[DataProvider('invalidWaitActionDataProvider')]
    #[DataProvider('invalidActionTypeDataProvider')]
    public function testValidateNotValid(ActionInterface $action, InvalidResultInterface $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->actionValidator->validate($action));
    }

    /**
     * @return array<mixed>
     */
    public static function invalidInteractionActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action: identifier invalid (element reference)' => [
                'action' => $actionParser->parse('click $elements.element_name', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $elements.element_name', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (attribute reference)' => [
                'action' => $actionParser->parse('click $elements.element_name.attribute_name', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $elements.element_name.attribute_name', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (attribute identifier)' => [
                'action' => $actionParser->parse('click $".selector".attribute_name', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $".selector".attribute_name', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (page element reference)' => [
                'action' => $actionParser->parse('click $page_import_name.elements.element_name', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $page_import_name.elements.element_name', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (browser property)' => [
                'action' => $actionParser->parse('click $browser.size', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $browser.size', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (page property)' => [
                'action' => $actionParser->parse('click $page.url', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $page.url', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (data parameter)' => [
                'action' => $actionParser->parse('click $data.key', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $data.key', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (environment parameter)' => [
                'action' => $actionParser->parse('click $env.KEY', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $env.KEY', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (quoted literal)' => [
                'action' => new Action('click "selector"', 0, 'click', '"selector"', '"selector"'),
                'expectedResult' => new InvalidResult(
                    new Action('click "selector"', 0, 'click', '"selector"', '"selector"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (literal)' => [
                'action' => new Action('click selector', 0, 'click', 'selector', 'selector'),
                'expectedResult' => new InvalidResult(
                    new Action('click selector', 0, 'click', 'selector', 'selector'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function invalidInputActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'input action: identifier invalid (element reference)' => [
                'action' => $actionParser->parse('set $elements.element_name to "value"', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $elements.element_name to "value"', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (attribute reference)' => [
                'action' => $actionParser->parse('set $elements.element_name.attribute_name to "value"', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $elements.element_name.attribute_name to "value"', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (attribute identifier)' => [
                'action' => $actionParser->parse('set $".selector".attribute_name to "value"', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $".selector".attribute_name to "value"', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (page element reference)' => [
                'action' => $actionParser->parse('set $page_import_name.elements.element_name to "value"', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $page_import_name.elements.element_name to "value"', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (browser property)' => [
                'action' => $actionParser->parse('set $browser.size to "value"', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $browser.size to "value"', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (page property)' => [
                'action' => $actionParser->parse('set $page.url to "value"', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $page.url to "value"', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (data parameter)' => [
                'action' => $actionParser->parse('set $data.key to "value"', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $data.key to "value"', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (environment parameter)' => [
                'action' => $actionParser->parse('set $env.KEY to "value"', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $env.KEY to "value"', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (quoted literal)' => [
                'action' => new Action(
                    'set "selector" to "value"',
                    0,
                    'set',
                    '"selector" to "value"',
                    '"selector"',
                    '"value"'
                ),
                'expectedResult' => new InvalidResult(
                    new Action(
                        'set "selector" to "value"',
                        0,
                        'set',
                        '"selector" to "value"',
                        '"selector"',
                        '"value"'
                    ),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: value invalid (unquoted value)' => [
                'action' => $actionParser->parse('set $".selector" to $page.address', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $".selector" to $page.address', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_VALUE,
                    new InvalidResult(
                        '$page.address',
                        ResultType::VALUE,
                        ValueValidator::REASON_PROPERTY_INVALID
                    )
                ),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function invalidWaitActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'wait action: value invalid (element reference)' => [
                'action' => $actionParser->parse('wait $elements.element_name', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('wait $elements.element_name', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_VALUE,
                    new InvalidResult(
                        '$elements.element_name',
                        ResultType::VALUE,
                        ValueValidator::REASON_INVALID
                    )
                ),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function invalidActionTypeDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'invalid action type' => [
                'action' => $actionParser->parse('invalid', 0),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('invalid', 0),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_TYPE
                ),
            ],
        ];
    }
}
