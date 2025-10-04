<?php

/**
 * This script converts Joomla to psr-12 coding standard
 *
 * @package    Joomla.Build
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

$tmpDir = dirname(__DIR__) . '/tmp/psr12';

$cleaned = [];

$json = file_get_contents($tmpDir . '/cleanup.json');

$data = json_decode($json, JSON_OBJECT_AS_ARRAY);

// Fixing the later issues in a file first should allow us to preserve the line value per error
$data = array_reverse($data);

foreach ($data as $error) {
    $file = file_get_contents($error['file']);
    switch ($error['cleanup']) {
        // Remove defined JEXEC statement, PSR-12 doesn't allow functional and symbol code in the same file
        case 'definedJEXEC':
            $file = str_replace([
                                    /**
                                     * I know this looks silly but makes it more clear what's happening
                                     * We remove the different types of execution check from files which
                                     * only defines symbols (like classes).
                                     *
                                     * The order is important.
                                     */
                                    "\defined('_JEXEC') || die();",
                                    "defined('_JEXEC') || die();",
                                    "\defined('_JEXEC') || die;",
                                    "defined('_JEXEC') || die;",
                                    "\defined('_JEXEC') or die();",
                                    "defined('_JEXEC') or die();",
                                    "\defined('_JEXEC') or die;",
                                    "defined('_JEXEC') or die;",
                                    "\defined('JPATH_BASE') or die();",
                                    "defined('JPATH_BASE') or die();",
                                    "\defined('JPATH_BASE') or die;",
                                    "defined('JPATH_BASE') or die;",
                                    /**
                                     * We have variants of comments in front of the 'defined die' statement
                                     * which we would like to remove too.
                                     *
                                     * The order is important.
                                     */
                                    "// No direct access.",
                                    "// No direct access",
                                    "// no direct access",
                                    "// Restrict direct access",
                                    "// Protect from unauthorized access",
                                ], '', $file);
            break;

        // Not all files need a namespace
        case 'MissingNamespace':
            // We search for the end of the first doc block and add the exception for this file
            $pos  = strpos($file, ' */');
            $file = substr_replace(
                $file,
                "\n * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace\n",
                $pos,
                0
            );

            break;
        // Not all classes have to be camelcase
        case 'ValidClassNameNotCamelCaps':
            // We search for the end of the first doc block and add the exception for this file
            $pos  = strpos($file, ' */');
            $file = substr_replace(
                $file,
                "\n * @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps\n",
                $pos,
                0
            );

            break;

        case 'ConstantVisibility':
            // add public to const declaration if defined in a class
            $fileContent                     = file($error['file']);
            $fileContent[$error['line'] - 1] = substr_replace($fileContent[$error['line'] - 1], 'public ', $error['column'] - 1, 0);
            $file                            = implode('', $fileContent);

            break;

        case 'SpaceAfterCloseBrace':
            // We only move single comments (starting with //) to the next line

            $fileContent = file($error['file']);

            $lineNo = $error['line'];

            // We skip blank lines
            do {
                $nextLine = ltrim($fileContent[$lineNo]);
                if (empty($nextLine)) {
                    $lineNo   = $lineNo + 1;
                    $nextLine = ltrim($fileContent[$lineNo]);
                }
            } while (empty($nextLine));

            $sourceLineStartNo = $lineNo;
            $sourceLineEndNo   = $lineNo;
            $found             = false;

            while (substr(ltrim($fileContent[$sourceLineEndNo]), 0, 2) === '//') {
                $sourceLineEndNo++;
                $found = true;
            }

            if ($sourceLineStartNo === $sourceLineEndNo) {
                if (substr(ltrim($fileContent[$sourceLineStartNo]), 0, 2) === '/*') {
                    while (substr(ltrim($fileContent[$sourceLineEndNo]), 0, 2) !== '*/') {
                        $sourceLineEndNo++;
                    }
                    $sourceLineEndNo++;
                    $found = true;
                }
            }

            if (!$found) {
                echo "Unrecoverable error while running SpaceAfterCloseBrace cleanup";
                var_dump($error['file'], $sourceLineStartNo, $sourceLineEndNo);
                die(1);
            }
            $targetLineNo = $sourceLineEndNo + 1;

            // Adjust the indentation to match the next line of code
            for ($indent = 0; $indent <= strlen($fileContent[$targetLineNo]); $indent++) {
                if ($fileContent[$targetLineNo][$indent] !== ' ') {
                    break;
                }
            }

            $replace = [];
            for ($i = $sourceLineStartNo; $i < $sourceLineEndNo; $i++) {
                $newLine = ltrim($fileContent[$i]);
                // Fix codeblocks not starting with /**
                if (substr($newLine, 0, 2) === '/*') {
                    $newLine = "/**\n";
                }

                $localIndent = $indent;
                if ($newLine[0] === '*') {
                    $localIndent++;
                }
                $replace[] = str_repeat(' ', $localIndent) . $newLine;
            }
            array_unshift($replace, $fileContent[$sourceLineEndNo]);

            array_splice($fileContent, $sourceLineStartNo, count($replace), $replace);

            $file = implode('', $fileContent);

            break;
    }

    file_put_contents($error['file'], $file);
    $cleaned[] = $error['file'] . ' ' . $error['cleanup'];
}

file_put_contents($tmpDir . '/cleaned.log', implode("\n", $cleaned));
