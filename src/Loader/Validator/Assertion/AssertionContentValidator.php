<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Validator\Assertion;

use SmartAssert\Compiler\Loader\Validator\ResultInterface;
use SmartAssert\Compiler\Loader\Validator\ValidResult;
use SmartAssert\Compiler\Loader\Validator\ValueValidator;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;

class AssertionContentValidator
{
    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ValueValidator $valueValidator;

    public function __construct(IdentifierTypeAnalyser $identifierTypeAnalyser, ValueValidator $valueValidator)
    {
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->valueValidator = $valueValidator;
    }

    public static function create(): AssertionContentValidator
    {
        return new AssertionContentValidator(
            IdentifierTypeAnalyser::create(),
            ValueValidator::create()
        );
    }

    public function validate(string $content): ResultInterface
    {
        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($content)) {
            return new ValidResult($content);
        }

        return $this->valueValidator->validate($content);
    }
}
