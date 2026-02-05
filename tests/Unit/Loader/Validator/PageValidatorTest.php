<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Resolver\PageResolver;
use SmartAssert\Compiler\Loader\Validator\InvalidResult;
use SmartAssert\Compiler\Loader\Validator\InvalidResultInterface;
use SmartAssert\Compiler\Loader\Validator\PageValidator;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Loader\Validator\ValidResult;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Model\Page\PageInterface;

class PageValidatorTest extends TestCase
{
    private PageValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = PageValidator::create();
    }

    #[DataProvider('validateIsValidDataProvider')]
    public function testValidateIsValid(PageInterface $page): void
    {
        $this->assertEquals(new ValidResult($page), $this->validator->validate($page));
    }

    /**
     * @return array<mixed>
     */
    public static function validateIsValidDataProvider(): array
    {
        $pageResolver = PageResolver::createResolver();

        return [
            'url only' => [
                'page' => new Page('import_name', 'http://example.com'),
            ],
            'url and identifiers' => [
                'page' => new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'input' => '$".input"',
                ]),
            ],
            'url and identifiers, parent >> child' => [
                'page' => new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'form_input' => '$form >> $".input"',
                ]),
            ],
            'url and identifiers, grandparent > parent > child' => [
                'page' => $pageResolver->resolve(new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'form_container' => '$form >> $".container"',
                    'form_input' => '$form_container >> $".input"',
                ])),
            ],
            'identifier with position' => [
                'page' => $pageResolver->resolve(new Page('import_name', 'http://example.com', [
                    'form' => '$".form":3',
                ])),
            ],
        ];
    }

    #[DataProvider('validateNotValidDataProvider')]
    public function testValidateNotValid(PageInterface $page, InvalidResultInterface $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->validator->validate($page));
    }

    /**
     * @return array<mixed>
     */
    public static function validateNotValidDataProvider(): array
    {
        return [
            'invalid identifiers: attribute identifier' => [
                'page' => new Page('import_name', 'http://example.com', [
                    'name' => '$".selector".attribute_name',
                ]),
                'expectedResult' => (new InvalidResult(
                    new Page('import_name', 'http://example.com', [
                        'name' => '$".selector".attribute_name',
                    ]),
                    ResultType::PAGE,
                    PageValidator::REASON_IDENTIFIER_INVALID
                ))->withContext([
                    PageValidator::CONTEXT_NAME => 'name',
                    PageValidator::CONTEXT_IDENTIFIER => '$".selector".attribute_name',
                ]),
            ],
            'invalid identifiers: element reference' => [
                'page' => new Page('import_name', 'http://example.com', [
                    'name' => '$elements.element_name',
                ]),
                'expectedResult' => (new InvalidResult(
                    new Page('import_name', 'http://example.com', [
                        'name' => '$elements.element_name',
                    ]),
                    ResultType::PAGE,
                    PageValidator::REASON_IDENTIFIER_INVALID
                ))->withContext([
                    PageValidator::CONTEXT_NAME => 'name',
                    PageValidator::CONTEXT_IDENTIFIER => '$elements.element_name',
                ]),
            ],
        ];
    }
}
