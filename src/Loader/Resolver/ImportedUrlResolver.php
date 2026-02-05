<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver;

use webignition\BasilModels\Model\Page\PageInterface;
use webignition\BasilModels\Model\PageUrlReference\PageUrlReference;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\ProviderInterface;

class ImportedUrlResolver
{
    public static function createResolver(): ImportedUrlResolver
    {
        return new ImportedUrlResolver();
    }

    /**
     * @param non-empty-string $url
     *
     * @return non-empty-string
     *
     * @throws UnknownItemException
     */
    public function resolve(string $url, ProviderInterface $pageProvider): string
    {
        $pageUrlReference = new PageUrlReference($url);
        if ($pageUrlReference->isValid()) {
            $page = $pageProvider->find($pageUrlReference->getImportName());

            if ($page instanceof PageInterface) {
                $url = (string) $page->getUrl();
            }
        }

        return $url;
    }
}
