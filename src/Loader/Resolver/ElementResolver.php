<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver;

use webignition\BasilModels\Model\AttributeReference\AttributeReference;
use webignition\BasilModels\Model\ElementReference\ElementReference;
use webignition\BasilModels\Model\PageElementReference\PageElementReference;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Identifier\IdentifierProviderInterface;
use webignition\BasilModels\Provider\Page\PageProviderInterface;

class ElementResolver
{
    public function __construct(
        private PageElementReferenceResolver $pageElementReferenceResolver
    ) {}

    public static function createResolver(): ElementResolver
    {
        return new ElementResolver(
            PageElementReferenceResolver::createResolver()
        );
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    public function resolve(
        string $value,
        PageProviderInterface $pageProvider,
        IdentifierProviderInterface $identifierProvider
    ): string {
        try {
            if (ElementReference::is($value)) {
                return $identifierProvider->find((new ElementReference($value))->getElementName());
            }

            if (AttributeReference::is($value)) {
                $attributeReference = new AttributeReference($value);
                $identifier = $identifierProvider->find($attributeReference->getElementName());

                return $identifier . '.' . $attributeReference->getAttributeName();
            }
        } catch (UnknownItemException $unknownIdentifierException) {
            throw new UnknownElementException($unknownIdentifierException->getName());
        }

        if (PageElementReference::is($value)) {
            return $this->pageElementReferenceResolver->resolve($value, $pageProvider);
        }

        return $value;
    }
}
