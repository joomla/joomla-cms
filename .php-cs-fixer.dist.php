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
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer
 * @see https://mlocati.github.io/php-cs-fixer-configurator/#version:3.0
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

// Only index the files in /libraries and no deeper, to prevent /libraries/vendor being indexed
$topFilesFinder = PhpCsFixer\Finder::create()
	->in(
		[
			__DIR__ . '/libraries'
		]
	)
	->files()
	->depth(0);

// Add all the core Joomla folders and append to this list the files indexed above from /libraries
$mainFinder = PhpCsFixer\Finder::create()
	->in(
		[
			__DIR__ . '/administrator',
			__DIR__ . '/api',
			__DIR__ . '/build',
			__DIR__ . '/cache',
			__DIR__ . '/cli',
			__DIR__ . '/components',
			__DIR__ . '/includes',
			__DIR__ . '/installation',
			__DIR__ . '/language',
			__DIR__ . '/libraries/src',
			__DIR__ . '/modules',
			__DIR__ . '/plugins',
			__DIR__ . '/templates',
			__DIR__ . '/tests',
			__DIR__ . '/layouts',
		]
	)
	->append($topFilesFinder);

$config = new PhpCsFixer\Config();
$config
	->setRiskyAllowed(true)
	->setIndent("\t")
	->setRules(
		[
			// psr-1
			'encoding'                              => true,
			// psr-2
			'elseif'                                => true,
			'single_blank_line_at_eof'              => true,
			'no_spaces_after_function_name'         => true,
			'blank_line_after_namespace'            => true,
			'line_ending'                           => true,
			'constant_case'                         => ['case' => 'lower'],
			'lowercase_keywords'                    => true,
			'method_argument_space'                 => true,
			'single_import_per_statement'           => true,
			'no_spaces_inside_parenthesis'          => true,
			'single_line_after_imports'             => true,
			'no_trailing_whitespace'                => true,
			// symfony
			'no_whitespace_before_comma_in_array'   => true,
			'whitespace_after_comma_in_array'       => true,
			'no_empty_statement'                    => true,
			'simplified_null_return'                => true,
			'no_extra_blank_lines'                  => true,
			'function_typehint_space'               => true,
			'include'                               => true,
			'no_alias_functions'                    => true,
			'no_trailing_comma_in_list_call'        => true,
			'trailing_comma_in_multiline'           => ['elements' => ['arrays']],
			'no_blank_lines_after_class_opening'    => true,
			'phpdoc_trim'                           => true,
			'blank_line_before_statement'           => ['statements' => ['return']],
			'no_trailing_comma_in_singleline_array' => true,
			'single_blank_line_before_namespace'    => true,
			'cast_spaces'                           => true,
			'no_unused_imports'                     => true,
			'no_whitespace_in_blank_line'           => true,
			// contrib
			'concat_space'                          => ['spacing' => 'one'],
			/**
			 * PHP 7+ zend_try_compile_special_func compiles certain PHP Functions to opcode which is faster
			 * @see https://github.com/php/php-src/blob/9dc947522186766db4a7e2d603703a2250797577/Zend/zend_compile.c#L4192
			 */
			'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced', 'strict' => true],
		]
	)
	->setFinder($mainFinder);

return $config;
