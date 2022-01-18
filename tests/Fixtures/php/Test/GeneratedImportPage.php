<?php

namespace SmartAssert\Compiler\Generated;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilModels\Test\Configuration;

class GeneratedImportPage extends AbstractBaseTest
{
    public static function setUpBeforeClass(): void
    {
        try {
            self::setClientManager(new ClientManager(
                new Configuration(
                    'chrome',
                    'http://example.com'
                )
            ));
            parent::setUpBeforeClass();
            self::$client->request('GET', 'http://example.com');
        } catch (\Throwable $exception) {
            self::staticSetLastException($exception);
        }
    }

    public function test1()
    {
        if (self::hasException()) {
            return;
        }
        $this->setBasilStepName('verify page is open');
        $this->setCurrentDataSet(null);

        // $page.url is "http://example.com" <- $page.url is $example_com.url
        $this->handledStatements[] = $this->assertionFactory->createFromJson('{
            "container": {
                "type": "resolved-assertion",
                "identifier": "$page.url",
                "value": "\\"http:\\/\\/example.com\\""
            },
            "statement": {
                "statement-type": "assertion",
                "source": "$page.url is $example_com.url",
                "identifier": "$page.url",
                "value": "$example_com.url",
                "operator": "is"
            }
        }');
        $this->setExpectedValue("http://example.com" ?? null);
        $this->setExaminedValue(self::$client->getCurrentURL() ?? null);
        $this->assertEquals(
            $this->getExpectedValue(),
            $this->getExaminedValue()
        );
    }
}
