<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude('Fixtures');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    '@PhpCsFixer' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'types_spaces' => false,
    'trailing_comma_in_multiline' => false,
    'php_unit_internal_class' => false,
    'php_unit_test_class_requires_covers' => false,
    # Following configuration added to make CI builds pass with ^3.9
    # @todo remove in #115
    'single_line_empty_body' => false,
])->setFinder($finder);
