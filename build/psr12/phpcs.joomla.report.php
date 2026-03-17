<?php

/**
 * A PHP CodeSniffer Report generated with preparation function for PSR-12 clean_errors
 *
 * @package    Joomla.Build
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Reports;

use PHP_CodeSniffer\Files\File;

use function array_keys;
use function array_merge;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_encode;
use function str_replace;

use const JSON_OBJECT_AS_ARRAY;
use const JSON_PRETTY_PRINT;

class Joomla implements \PHP_CodeSniffer\Reports\Report
{
    private $tmpDir = __DIR__ . '/../tmp/psr12';

    private $html = '';

    private $preProcessing = [];

    /**
     * Generate a partial report for a single processed file.
     *
     * Function should return TRUE if it printed or stored data about the file
     * and FALSE if it ignored the file. Returning TRUE indicates that the file and
     * its data should be counted in the grand totals.
     *
     * @param   array                  $report       Prepared report data.
     * @param   \PHP_CodeSniffer\File  $phpcsFile    The file being reported on.
     * @param   bool                   $showSources  Show sources?
     * @param   int                    $width        Maximum allowed line width.
     *
     * @return bool
     */
    public function generateFileReport($report, File $phpcsFile, $showSources = false, $width = 80)
    {
        if ($report['errors'] === 0 && $report['warnings'] === 0) {
            return false;
        }

        $template   = [
            'headline' => $report['filename'],
            'text'     => 'Errors: ' . $report['errors'] . ' Warnings: ' . $report['warnings'] . ' Fixable: ' . $report['fixable'],
        ];

        $this->html = <<<HTML
                    <div class="span12">
                        <h3>{$template['headline']}</h3>
                        <p>{$template['text']}</p>
                    HTML;

        foreach ($report['messages'] as $line => $lineErrors) {
            foreach ($lineErrors as $column => $colErrors) {
                foreach ($colErrors as $error) {
                    $error['type'] = strtolower($error['type']);
                    if ($phpcsFile->config->encoding !== 'utf-8') {
                        $error['message'] = iconv($phpcsFile->config->encoding, 'utf-8', $error['message']);
                    }

                    $error['fixable'] = $error['fixable'] === true ? 'Yes' : 'No';

                    $this->html .= <<<HTML
                                <div class="highlight highlight-text-html-basic">
                                  Line: <b>$line</b>
                                  Column: <b>$column</b>
                                  Fixable: <b>{$error['fixable']}</b>
                                  Severity: <b>{$error['severity']}</b>
                                  Rule: <b>{$error['source']}</b>
                                  <pre>{$error['message']}</pre>
                                </div>
                                HTML;
                    $this->prepareProcessing($report['filename'], $phpcsFile, $line, $column, $error);
                }
            }
        }

        $this->html .= <<<HTML
                    </div>
                    HTML;

        $this->writeFile();

        return true;
    }

    private function prepareProcessing($file, $phpcsFile, $line, $column, $error)
    {
        switch ($error['source']) {
            case 'PSR1.Files.SideEffects.FoundWithSymbols':
                $fileContent = file_get_contents($file);

                if (
                    strpos($fileContent, "defined('_JEXEC')") !== false
                    || strpos($fileContent, "defined('JPATH_BASE')") !== false
                ) {
                    $this->preProcessing[] = [
                        'file' => $file,
                        'line' => $line,
                        'column' => $column,
                        'cleanup' => 'definedJEXEC',
                    ];
                } else {
                    $targetFile = $this->tmpDir . '/' . $error['source'] . '.txt';
                    $fileContent = '';
                    if (file_exists($targetFile)) {
                        $fileContent = file_get_contents($targetFile);
                    }

                    static $replace = null;

                    if ($replace === null) {
                        $replace = [
                            "\\" => '/',
                            dirname(dirname(__DIR__)) . '/' => '',
                            '.' => '\.',
                        ];
                    }

                    $fileContent .= "        <exclude-pattern>" . str_replace(array_keys($replace), $replace, $file) . "</exclude-pattern>\n";
                    file_put_contents($targetFile, $fileContent);
                }
                break;

            case 'PSR1.Classes.ClassDeclaration.MissingNamespace':
                $this->preProcessing[] = [
                    'file' => $file,
                    'line' => $line,
                    'column' => $column,
                    'cleanup' => 'MissingNamespace',
                ];
                break;

            case 'Squiz.Classes.ValidClassName.NotCamelCaps':
                if (strpos($file, 'localise') !== false) {
                    $this->preProcessing[] = [
                        'file'    => $file,
                        'line'    => $line,
                        'column'  => $column,
                        'cleanup' => 'ValidClassNameNotCamelCaps',
                    ];
                }
                break;

            case 'Squiz.ControlStructures.ControlSignature.SpaceAfterCloseBrace':
                $this->preProcessing[] = [
                    'file' => $file,
                    'line' => $line,
                    'column' => $column,
                    'cleanup' => 'SpaceAfterCloseBrace',
                ];
                break;

            case 'PSR12.Properties.ConstantVisibility.NotFound':
                $this->preProcessing[] = [
                    'file' => $file,
                    'line' => $line,
                    'column' => $column,
                    'cleanup' => 'ConstantVisibility',
                ];
                break;

            case 'PSR2.Classes.PropertyDeclaration.Underscore':
            case 'PSR2.Methods.MethodDeclaration.Underscore':
            case 'PSR1.Classes.ClassDeclaration.MultipleClasses':
            case 'PSR1.Methods.CamelCapsMethodName.NotCamelCaps':
                $targetFile = $this->tmpDir . '/' . $error['source'] . '.txt';
                $fileContent = '';
                if (file_exists($targetFile)) {
                    $fileContent = file_get_contents($targetFile);
                }

                static $replace = null;

                if ($replace === null) {
                    $replace = [
                        "\\" => '/',
                        dirname(dirname(__DIR__)) . '/' => '',
                        '.' => '\.',
                    ];
                }

                $fileContent .= "        <exclude-pattern>" . str_replace(array_keys($replace), $replace, $file) . "</exclude-pattern>\n";
                file_put_contents($targetFile, $fileContent);
                break;
        }
    }

    /**
     * Prints all violations for processed files, in a proprietary XML format.
     *
     * @param   string  $cachedData     Any partial report data that was returned from
     *                                  generateFileReport during the run.
     * @param   int     $totalFiles     Total number of files processed during the run.
     * @param   int     $totalErrors    Total number of errors found during the run.
     * @param   int     $totalWarnings  Total number of warnings found during the run.
     * @param   int     $totalFixable   Total number of problems that can be fixed.
     * @param   bool    $showSources    Show sources?
     * @param   int     $width          Maximum allowed line width.
     * @param   bool    $interactive    Are we running in interactive mode?
     * @param   bool    $toScreen       Is the report being printed to screen?
     *
     * @return void
     */
    public function generate(
        $cachedData,
        $totalFiles,
        $totalErrors,
        $totalWarnings,
        $totalFixable,
        $showSources = false,
        $width = 80,
        $interactive = false,
        $toScreen = true
    ) {
        $preprocessing = [];
        if (file_exists($this->tmpDir . '/cleanup.json')) {
            $preprocessing = json_decode(file_get_contents($this->tmpDir . '/cleanup.json'), JSON_OBJECT_AS_ARRAY);
        }

        $preprocessing = array_merge($this->preProcessing, $preprocessing);
        file_put_contents($this->tmpDir . '/cleanup.json', json_encode($preprocessing, JSON_PRETTY_PRINT));
    }

    private function getTemplate($section)
    {
        $sections = [
            'header' => <<<HTML
                        <html lang="en">
                        <head>
                            <title>Report</title>
                            <link href="https://cdn.joomla.org/template/css/template_3.0.0.min.css" rel="stylesheet">
                        </head>
                        <body>
                        <main id="content" class="span12">
                            <div class="github-documentation">
                                <div class="page-header">
                                    <h1>
                                        Joomla! Coding Standards Check</h1>
                                </div>

                                <div class="row-fluid">
                        <div class="span12">Check</div>
                        HTML,
            'footer' => <<<HTML
                                </div>
                            </div>
                        </main>
                        </body>
                        </html>
                        HTML,
            'line'   => <<<HTML
                        <div class="span12">
                            <h3>%HEADLINE%</h3>
                            <p>%TEXT%</p>
                            <div class="highlight highlight-text-html-basic">
                              <pre>%ERROR%</pre>
                            </div>
                        </div>
                        HTML
        ];

        return $sections[$section];
    }

    private function htmlAddBlock($headline, $text, $error)
    {
        $line = $this->getTemplate('line');

        $replace = [
            '%HEADLINE%' => $headline,
            '%TEXT%' => $text,
            '%ERROR%' => $error,
        ];

        $this->html .= str_replace(array_keys($replace), $replace, $line);
    }

    private function writeFile()
    {
        $file = $this->tmpDir . '/result.html';

        if (file_exists($file)) {
            $html = file_get_contents($file);
        } else {
            $html = $this->getTemplate('header');
            $html .= '<span class="hidden">%PHPCS_NEXT_BLOCK%</span>';
            $html .= $this->getTemplate('footer');
        }

        $html = str_replace('<span class="hidden">%PHPCS_NEXT_BLOCK%</span>', $this->html . '<span class="hidden">%PHPCS_NEXT_BLOCK%</span>', $html);

        file_put_contents($this->tmpDir . '/result.html', $html);
    }
}
