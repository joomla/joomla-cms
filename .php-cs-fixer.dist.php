<?php

/**
 * @package    Joomla.Site
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is the configuration file for php-cs-fixer
 *
 * @link https://github.com/FriendsOfPHP/PHP-CS-Fixer
 * @link https://mlocati.github.io/php-cs-fixer-configurator/#version:3.95
 *
 *
 * If you would like to run the automated clean up, then open a command line and type one of the commands below
 *
 * To run a quick dry run to see the files that would be modified:
 *
 *        ./libraries/vendor/bin/php-cs-fixer fix --dry-run
 *
 * To run a full check, with automated fixing of each problem :
 *
 *        ./libraries/vendor/bin/php-cs-fixer fix
 *
 * You can run the clean up on a single file if you need to, this is faster
 *
 *        ./libraries/vendor/bin/php-cs-fixer fix --dry-run administrator/index.php
 *        ./libraries/vendor/bin/php-cs-fixer fix administrator/index.php
 */

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Config\RuleCustomisationPolicyInterface;
use PhpCsFixer\Finder;

// Add all the core Joomla folders
$finder = (new Finder())
    ->in(__DIR__)
    ->exclude([
        'administrator/cache',
        'administrator/logs',
        'build/psr12',
        'node_modules',
        'libraries/php-encryption',
        'libraries/phpass',
    ])
    ->notPath([
        'configuration.php',
    ])
;

final class JoomlaPolicy implements RuleCustomisationPolicyInterface
{
    public function getPolicyVersionForCache(): string
    {
        return hash_file('xxh128', __FILE__);
    }

    public function getRuleCustomisers(): array
    {
        $disableLayoutFile = [
            'binary_operator_spaces',
            'native_function_invocation',
            'no_trailing_whitespace_in_comment',
            'single_space_around_construct',
            'statement_indentation',
            // @todo Remove in next major version to avoid having to update too many template overrides.
            'braces_position',
            'combine_consecutive_issets',
            'no_spaces_after_function_name',
            'no_unneeded_control_parentheses',
            'single_line_after_imports',
            'spaces_inside_parentheses',
            'ternary_operator_spaces',
            'trailing_comma_in_multiline',
        ];

        $ruleCustomisers = [];
        foreach ($disableLayoutFile as $rule) {
            $ruleCustomisers[$rule] = static function (\SplFileInfo $file): bool {
                return !self::isLayoutFile($file);
            };
        }

        return $ruleCustomisers;
    }

    private static function isLayoutFile(\SplFileInfo $file): bool
    {
        return str_contains($file->getPathname(), \DIRECTORY_SEPARATOR . 'tmpl' . \DIRECTORY_SEPARATOR)
            || str_contains($file->getPathname(), \DIRECTORY_SEPARATOR . 'layouts' . \DIRECTORY_SEPARATOR)
            || str_contains($file->getPathname(), \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . 'atum' . \DIRECTORY_SEPARATOR)
            || str_contains($file->getPathname(), \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . 'cassiopeia' . \DIRECTORY_SEPARATOR)
            || str_contains($file->getPathname(), \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . 'cassiopeia_extended' . \DIRECTORY_SEPARATOR);
    }
}

$config = (new Config())
    ->setRiskyAllowed(true)
    ->setHideProgress(false)
    ->setUsingCache(false)
    ->setRules(
        [
            // Basic ruleset is PSR 12
            '@PSR12'                                           => true,
            // Short array syntax
            'array_syntax'                                     => ['syntax' => 'short'],
            // List of values separated by a comma is contained on a single line should not have a trailing comma like [$foo, $bar,] = ...
            'no_trailing_comma_in_singleline'                  => true,
            // Arrays on multiline should have a trailing comma
            'trailing_comma_in_multiline'                      => ['elements' => ['arrays']],
            // Align elements in multiline array and variable declarations on new lines below each other
            'binary_operator_spaces'                           => ['operators' => ['=>' => 'align_single_space_minimal', '=' => 'align', '??=' => 'align']],
            // The "No break" comment in switch statements
            'no_break_comment'                                 => ['comment_text' => 'No break'],
            // Remove unused imports
            'no_unused_imports'                                => true,
            // Classes from the global namespace should not be imported
            'global_namespace_import'                          => ['import_classes' => false, 'import_constants' => false, 'import_functions' => false],
            // Alpha order imports
            'ordered_imports'                                  => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
            // There should not be useless else cases
            'no_useless_else'                                  => true,
            // Native function invocation
            'native_function_invocation'                       => ['include' => ['@compiler_optimized']],
            // Adds null to type declarations when parameter have a default null value
            'nullable_type_declaration_for_default_null_value' => true,
            // Removes unneeded parentheses around control statements
            'no_unneeded_control_parentheses'                  => true,
            // Using isset($var) && multiple times should be done in one call.
            'combine_consecutive_issets'                       => true,
            // Calling unset on multiple items should be done in one call
            'combine_consecutive_unsets'                       => true,
            // There must be no sprintf calls with only the first argument
            'no_useless_sprintf'                               => true,
        ]
    )
    ->setRuleCustomisationPolicy(new JoomlaPolicy())
    ->setFinder($finder)
;

return $config;
