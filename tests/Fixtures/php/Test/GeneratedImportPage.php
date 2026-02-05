<?php

namespace SmartAssert\Compiler\Generated;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BaseBasilTestCase\Attribute\Statements;
use webignition\BaseBasilTestCase\Attribute\StepName;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\BaseBasilTestCase\Enum\StatementStage;

class GeneratedImportPage extends AbstractBaseTest
{
    public static function setUpBeforeClass(): void
    {
        self::setClientManager(new ClientManager('chrome'));
        parent::setUpBeforeClass();
        self::$client->request('GET', 'http://example.com');
    }

    #[StepName('verify page is open')]
    #[Statements([
        '{
            "container": {
                "identifier": "$page.url",
                "value": "\"http:\/\/example.com\"",
                "type": "resolved-assertion"
            },
            "statement": {
                "statement-type": "assertion",
                "source": "$page.url is $example_com.url",
                "index": 0,
                "identifier": "$page.url",
                "value": "$example_com.url",
                "operator": "is"
            }
        }',
    ])]
    public function test1(): void
    {
        // $page.url is "http://example.com" <- $page.url is $example_com.url
        $statement_0 = '{
            "container": {
                "identifier": "$page.url",
                "value": "\"http:\/\/example.com\"",
                "type": "resolved-assertion"
            },
            "statement": {
                "statement-type": "assertion",
                "source": "$page.url is $example_com.url",
                "index": 0,
                "identifier": "$page.url",
                "value": "$example_com.url",
                "operator": "is"
            }
        }';

        try {
            $expectedValue = "http://example.com";
            $examinedValue = self::$client->getCurrentURL();
        } catch (\Throwable $exception) {
            $this->fail(
                self::$messageFactory->createFailureMessage($statement_0, $exception, StatementStage::SETUP),
            );
        }

        $this->assertEquals(
            $expectedValue,
            $examinedValue,
            self::$messageFactory->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
        );
    }
}
