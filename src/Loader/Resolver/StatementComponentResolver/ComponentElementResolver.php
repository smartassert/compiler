<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver\StatementComponentResolver;

use SmartAssert\Compiler\Loader\Resolver\ElementResolver;
use SmartAssert\Compiler\Loader\Resolver\ResolvedComponent;
use SmartAssert\Compiler\Loader\Resolver\UnknownElementException;
use SmartAssert\Compiler\Loader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Identifier\IdentifierProviderInterface;
use webignition\BasilModels\Provider\Page\PageProviderInterface;

class ComponentElementResolver implements StatementComponentResolverInterface
{
    public function __construct(
        private ElementResolver $elementResolver
    ) {}

    public static function createResolver(): self
    {
        return new ComponentElementResolver(
            ElementResolver::createResolver()
        );
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    public function resolve(
        ?string $data,
        PageProviderInterface $pageProvider,
        IdentifierProviderInterface $identifierProvider
    ): ?ResolvedComponent {
        if (is_string($data)) {
            return new ResolvedComponent(
                $data,
                $this->elementResolver->resolve($data, $pageProvider, $identifierProvider)
            );
        }

        return null;
    }
}
