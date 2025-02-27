<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude('tests');

$config = (new Config())->setRules([
    '@Symfony' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'cast_spaces' => [
        'space' => 'single',
    ],
    'error_suppression' => [
        'mute_deprecation_error' => false,
        'noise_remaining_usages' => false,
        'noise_remaining_usages_exclude' => [],
    ],
    'function_to_constant' => false,
    'no_alias_functions' => false,
    'phpdoc_summary' => false,
    'protected_to_private' => false,
    'self_accessor' => false,
    'yoda_style' => false,
    'non_printable_character' => true,
    'no_superfluous_phpdoc_tags' => false,
    # to fix
    'blank_line_after_opening_tag' => false,
    'fully_qualified_strict_types' => false,
    'array_indentation' => false,
    'operator_linebreak' => false,
    'trailing_comma_in_multiline' => false,
    'phpdoc_align' => false,
    'global_namespace_import' => false,
    'visibility_required' => false,
    'phpdoc_order' => false,
    'phpdoc_separation' => false,
    'phpdoc_trim' => false,
    'phpdoc_trim_consecutive_blank_line_separation' => false,
    'single_line_comment_spacing' => false,
    'simple_to_complex_string_variable' => false,
    'phpdoc_no_empty_return' => false,
    'blank_line_before_statement' => false,
    'no_null_property_initialization' => false,
    'statement_indentation' => false,
    'nullable_type_declaration_for_default_null_value' => false,
]);

return $config->setUsingCache(false)->setFinder($finder);
