<?php

/**
 * This file is used to check if the exclude patterns in a PHPCS ruleset XML file are still relevant,
 * i.e. the files or folders still exist, and optionally fix the XML file.
 *
 * This script has three optional parameters:
 * --file <path> - Path to the PHPCS ruleset XML file to be checked, defaults to 'ruleset.xml' in the parent folder.
 * --fix         - Fix the XML file if any obsolete exclude patterns were found..
 * --help        - Show the help and exit.
 *
 * @package    Joomla.Build
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

function usage($command)
{
    echo PHP_EOL;
    echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
    echo PHP_EOL;
    echo "[options]:" . PHP_EOL;
    echo "--file <path>:\tPath to the PHPCS ruleset XML file to be checked, defaults to '" . dirname(__DIR__) . "/ruleset.xml'." . PHP_EOL;
    echo "--fix:\t\tFix the XML file if any obsolete exclude patterns were found." . PHP_EOL;
    echo "--help:\t\tShow this help output." . PHP_EOL;
    echo PHP_EOL;
}

$options = getopt('', ['file:', 'fix', 'help']);

if (isset($options['help'])) {
    usage($argv[0]);

    exit(0);
}

$rulesetFile = $options['file'] ?? dirname(__DIR__) . '/ruleset.xml';

// Exclude patterns to be skipped from the check because the file or folder might not exist
$ignoreList = [
    '^configuration.php',
    '^libraries/vendor/*',
    '^media/*',
    '^node_modules/*',
];

echo "Checking file '" . $rulesetFile . "' ..." . PHP_EOL;

$rulesetLines     = file($rulesetFile, FILE_IGNORE_NEW_LINES);
$obsoleteLineIdxs = [];

foreach ($rulesetLines as $line => $text) {
    if (!preg_match('/^(?:\s*<exclude-pattern type="relative">)(.*)(?:<\/exclude-pattern>\s*)$/', $text, $matches)) {
        continue;
    }

    if (in_array($matches[1], $ignoreList)) {
        continue;
    }

    // Unescape dots
    $path = str_replace('\.', '.', $matches[1]);

    // Remove start of string anchor if used
    if (substr($path, 0, 1) === '^') {
        $path = substr($path, 1);
    }

    // Remove asterisk from the end of paths of folders
    if (substr($path, -2) === '/*') {
        $path = substr($path, 0, -1);
    }

    if (substr($path, -1) === '/') {
        if (!is_dir(dirname(__DIR__) . '/' . $path)) {
            echo 'Line no. ' . $line + 1 . ': Folder "' . $path . '" doesn\'t exist.' . PHP_EOL;
            $obsoleteLineIdxs[] = $line;
        }
    } elseif (!is_file(dirname(__DIR__) . '/' . $path)) {
        echo 'Line no. ' . $line + 1 . ': File "' . $path . '" doesn\'t exist.' . PHP_EOL;
        $obsoleteLineIdxs[] = $line;
    }
}

echo "... done." . PHP_EOL;

if (!count($obsoleteLineIdxs)) {
    echo "No obsolete lines found." . PHP_EOL;

    exit(0);
}

if (!isset($options['fix'])) {
    exit(0);
}

foreach ($obsoleteLineIdxs as $line) {
    unset($rulesetLines[$line]);
}

echo "Updating file '" . $rulesetFile . "' ..." . PHP_EOL;

file_put_contents($rulesetFile, implode(PHP_EOL, $rulesetLines));

echo "... done." . PHP_EOL;
