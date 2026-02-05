<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Model;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;

class DependencyVariables
{
    public function __construct(
        private string $domNavigatorCrawlerName,
        private string $environmentVariableArrayName,
        private string $pantherClientName,
        private string $pantherCrawlerName,
        private string $phpUnitTestCaseName,
        private string $webDriverElementInspectorName,
        private string $webDriverElementMutatorName,
        private string $messaageFactory,
    ) {}

    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        return [
            DependencyName::DOM_CRAWLER_NAVIGATOR->value => $this->domNavigatorCrawlerName,
            DependencyName::ENVIRONMENT_VARIABLE_ARRAY->value => $this->environmentVariableArrayName,
            DependencyName::PANTHER_CLIENT->value => $this->pantherClientName,
            DependencyName::PANTHER_CRAWLER->value => $this->pantherCrawlerName,
            DependencyName::PHPUNIT_TEST_CASE->value => $this->phpUnitTestCaseName,
            DependencyName::WEBDRIVER_ELEMENT_INSPECTOR->value => $this->webDriverElementInspectorName,
            DependencyName::WEBDRIVER_ELEMENT_MUTATOR->value => $this->webDriverElementMutatorName,
            DependencyName::MESSAGE_FACTORY->value => $this->messaageFactory,
        ];
    }
}
