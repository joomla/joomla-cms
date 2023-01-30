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

// Add all the core Joomla folders
$finder = PhpCsFixer\Finder::create()
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
            __DIR__ . '/tests'
        ]
    )
    // Ignore template files as PHP CS fixer can't handle them properly
    // https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/3702#issuecomment-396717120
    ->notPath('/tmpl/')
    ->notPath('/layouts/')
    ->notPath('/cassiopeia/')
    ->notPath('/atum/');

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setHideProgress(false)
    ->setUsingCache(false)
    ->setRules(
        [
            '@PSR12'                         => true,
            'array_syntax'                   => ['syntax' => 'short'],
            'no_trailing_comma_in_list_call' => true,
            'trailing_comma_in_multiline'    => ['elements' => ['arrays']],
        ]
    )
    ->setFinder($finder);

return $config;
