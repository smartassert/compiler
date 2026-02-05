<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver;

use webignition\BasilModels\Model\Page\PageInterface;
use webignition\BasilModels\Model\PageElementReference\PageElementReference;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\ProviderInterface;

class PageElementReferenceResolver
{
    public function __construct(
        private PageResolver $pageResolver
    ) {}

    public static function createResolver(): PageElementReferenceResolver
    {
        return new PageElementReferenceResolver(
            PageResolver::createResolver()
        );
    }

    /**
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    public function resolve(
        string $pageElementReference,
        ProviderInterface $pageProvider
    ): string {
        $model = new PageElementReference(ltrim($pageElementReference, '$'));

        $page = $pageProvider->find($model->getImportName());

        if ($page instanceof PageInterface) {
            $page = $this->pageResolver->resolve($page);
            $identifier = $page->getIdentifier($model->getElementName());

            if (is_string($identifier)) {
                $attributeName = $model->getAttributeName();

                return '' === $attributeName
                    ? $identifier
                    : $identifier . '.' . $attributeName;
            }
        }

        throw new UnknownPageElementException($model->getImportName(), $model->getElementName());
    }
}
