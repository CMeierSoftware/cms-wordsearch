<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'autogenerated_content',
        'tests/fixtures',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'align_multiline_comment' => ['comment_type' => 'all_multiline'],
        'phpdoc_align' => ['align' => 'left'],
        'concat_space' => ['spacing' => 'one'],
        'return_type_declaration' => ['space_before' => 'none'],
        'no_unused_imports' => true,
        'single_import_per_statement' => ['group_to_single_imports' => true],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_mixed_echo_print' => ['use' => 'echo'],
        'array_syntax' => ['syntax' => 'short'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'trim_array_spaces' => true,
        'octal_notation' => true,
        'declare_strict_types' => true,
        'native_constant_invocation' => false,
        'native_function_invocation' => false,
    ])
    ->setFinder($finder)
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
;
