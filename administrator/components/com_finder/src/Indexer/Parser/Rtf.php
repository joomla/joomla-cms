<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer\Parser;

use Joomla\Component\Finder\Administrator\Indexer\Parser;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * RTF Parser class for the Finder indexer package.
 *
 * @since  2.5
 */
class Rtf extends Parser
{
    /**
     * Destinations which don't contain text of the document.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private const SKIPPED_DESTINATIONS = [
        'author', 'bkmkend', 'bkmkstart', 'buptim', 'colorschememapping', 'colortbl', 'comment', 'company', 'creatim',
        'datastore', 'doccomm', 'filetbl', 'fldinst', 'fonttbl', 'footer', 'footerf', 'footerl', 'footerr', 'generator',
        'header', 'headerf', 'headerl', 'headerr', 'info', 'keywords', 'latentstyles', 'listoverridetable', 'listtable',
        'manager', 'nonshppict', 'object', 'operator', 'pgdsctbl', 'pict', 'printim', 'private', 'revtbl', 'revtim',
        'rsidtbl', 'stylesheet', 'subject', 'themedata', 'title', 'xmlnstbl',
    ];

    /**
     * Control words which stand for a character.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    private const CHARACTERS = [
        'bullet'    => "\u{2022}",
        'cell'      => ' ',
        'emdash'    => "\u{2014}",
        'emspace'   => ' ',
        'endash'    => "\u{2013}",
        'enspace'   => ' ',
        'ldblquote' => "\u{201C}",
        'line'      => "\n",
        'lquote'    => "\u{2018}",
        'page'      => "\n",
        'par'       => "\n",
        'qmspace'   => ' ',
        'rdblquote' => "\u{201D}",
        'row'       => "\n",
        'rquote'    => "\u{2019}",
        'sect'      => "\n",
        'tab'       => ' ',
    ];

    /**
     * Codepages of the font character sets (\fcharsetN).
     *
     * @var    array<int, int>
     * @since  __DEPLOY_VERSION__
     */
    private const CHARSET_CODEPAGES = [
        77  => 10000,
        128 => 932,
        129 => 949,
        134 => 936,
        136 => 950,
        161 => 1253,
        162 => 1254,
        163 => 1258,
        177 => 1255,
        178 => 1256,
        186 => 1257,
        204 => 1251,
        222 => 874,
        238 => 1250,
        254 => 437,
        255 => 850,
    ];

    /**
     * Names of the codepages for mbstring (null if mbstring doesn't support it) and iconv.
     *
     * @var    array<int, array{?string, string}>
     * @since  __DEPLOY_VERSION__
     */
    private const ENCODINGS = [
        437   => [null, 'CP437'],
        850   => ['CP850', 'CP850'],
        866   => ['CP866', 'CP866'],
        874   => [null, 'CP874'],
        932   => ['SJIS-win', 'CP932'],
        936   => ['CP936', 'CP936'],
        949   => ['UHC', 'CP949'],
        950   => ['CP950', 'CP950'],
        1250  => [null, 'CP1250'],
        1251  => ['Windows-1251', 'CP1251'],
        1252  => ['Windows-1252', 'CP1252'],
        1253  => [null, 'CP1253'],
        1254  => ['Windows-1254', 'CP1254'],
        1255  => [null, 'CP1255'],
        1256  => [null, 'CP1256'],
        1257  => [null, 'CP1257'],
        1258  => [null, 'CP1258'],
        10000 => [null, 'MACINTOSH'],
        65001 => ['UTF-8', 'UTF-8'],
    ];

    /**
     * Method to parse RTF input and extract the plain text. RTF consists of nested groups,
     * which can't be split into chunks, so the input is processed in one go.
     *
     * @param   string  $input  The input to parse.
     *
     * @return  string  The plain text input.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function parse($input)
    {
        return $this->process((string) $input);
    }

    /**
     * Method to process RTF input and extract the plain text.
     *
     * @param   string  $input  The input to process.
     *
     * @return  string  The plain text input.
     *
     * @since   2.5
     */
    protected function process($input)
    {
        $length = \strlen($input);
        $output = '';

        // Text in the codepage of the current font, which is converted to UTF-8 in one go
        $bytes = '';

        // Codepage of the document, the codepages of the fonts and the default font
        $codepage    = 1252;
        $fonts       = [];
        $defaultFont = null;
        $fontNumber  = null;

        // State of the current group and of the enclosing groups
        $state = ['skip' => false, 'fonttbl' => false, 'font' => null, 'uc' => 1];
        $stack = [];

        // Number of fallback characters to skip after a \uN character
        $skipChars = 0;

        $flush = function () use (&$bytes, &$output, &$state, &$fonts, &$defaultFont, &$codepage) {
            if ($bytes !== '') {
                $output .= $this->decode($bytes, $fonts[$state['font'] ?? $defaultFont] ?? $codepage);
                $bytes = '';
            }
        };

        $i = 0;

        while ($i < $length) {
            $char = $input[$i];

            if ($char === '{') {
                $flush();
                $stack[]   = $state;
                $skipChars = 0;
                $i++;

                continue;
            }

            if ($char === '}') {
                $flush();
                $state     = array_pop($stack) ?? $state;
                $skipChars = 0;
                $i++;

                continue;
            }

            if ($char === "\r" || $char === "\n") {
                $i++;

                continue;
            }

            if ($char !== '\\') {
                // A run of text up to the next special character
                $run = strcspn($input, "\\{}\r\n", $i);

                if ($skipChars > 0) {
                    $dropped = min($skipChars, $run);
                    $skipChars -= $dropped;
                    $i += $dropped;
                    $run -= $dropped;
                }

                if (!$state['skip']) {
                    $bytes .= substr($input, $i, $run);
                }

                $i += $run;

                continue;
            }

            $next = $input[$i + 1] ?? '';

            // \'hh is a character in the codepage of the current font
            if ($next === "'") {
                $hex = substr($input, $i + 2, 2);
                $i += 4;

                if ($skipChars > 0) {
                    $skipChars--;
                } elseif (!$state['skip'] && ctype_xdigit($hex)) {
                    $bytes .= \chr(hexdec($hex));
                }

                continue;
            }

            // Control symbols
            if (!ctype_alpha($next)) {
                $i += 2;

                if ($next === '*') {
                    // An optional destination which is unknown to us
                    $state['skip'] = true;
                } elseif (!$state['skip']) {
                    switch ($next) {
                        case '\\':
                        case '{':
                        case '}':
                            $bytes .= $next;
                            break;

                        case '~':
                            $bytes .= ' ';
                            break;

                        case '_':
                            $bytes .= '-';
                            break;

                        case "\r":
                        case "\n":
                            $bytes .= "\n";
                            break;
                    }
                }

                continue;
            }

            // Control words: letters, an optional numeric parameter and an optional space as delimiter
            preg_match('/\G([a-zA-Z]{1,32})(-?\d{1,10})? ?/', $input, $matches, 0, $i + 1);
            $i += 1 + \strlen($matches[0]);
            $word      = $matches[1];
            $parameter = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : null;

            switch ($word) {
                case 'ansicpg':
                    $codepage = $parameter ?: $codepage;
                    break;

                case 'mac':
                    $codepage = 10000;
                    break;

                case 'pc':
                    $codepage = 437;
                    break;

                case 'pca':
                    $codepage = 850;
                    break;

                case 'deff':
                    $defaultFont = $parameter;
                    break;

                case 'f':
                    if ($state['fonttbl']) {
                        $fontNumber = $parameter;
                    } else {
                        $flush();
                        $state['font'] = $parameter;
                    }
                    break;

                case 'fcharset':
                    if ($state['fonttbl'] && $fontNumber !== null && isset(self::CHARSET_CODEPAGES[$parameter])) {
                        $fonts[$fontNumber] = self::CHARSET_CODEPAGES[$parameter];
                    }
                    break;

                case 'cpg':
                    if ($state['fonttbl'] && $fontNumber !== null && $parameter) {
                        $fonts[$fontNumber] = $parameter;
                    }
                    break;

                case 'plain':
                    $flush();
                    $state['font'] = null;
                    break;

                case 'uc':
                    $state['uc'] = max(0, $parameter ?? 1);
                    break;

                case 'u':
                    if ($parameter !== null) {
                        $unicodeChar = mb_chr($parameter < 0 ? $parameter + 65536 : $parameter, 'UTF-8');

                        if (!$state['skip'] && $unicodeChar !== false) {
                            $flush();
                            $output .= $unicodeChar;
                        }

                        $skipChars = $state['uc'];
                    }
                    break;

                case 'bin':
                    // Binary data of the given length
                    $i += max(0, (int) $parameter);
                    break;

                default:
                    if (\in_array($word, self::SKIPPED_DESTINATIONS, true)) {
                        $state['skip']    = true;
                        $state['fonttbl'] = $word === 'fonttbl';
                    } elseif (!$state['skip'] && isset(self::CHARACTERS[$word])) {
                        $flush();
                        $output .= self::CHARACTERS[$word];
                    }
            }
        }

        $flush();

        return $output;
    }

    /**
     * Converts text from the given codepage to UTF-8.
     *
     * @param   string  $bytes     The text in the codepage.
     * @param   int     $codepage  The codepage.
     *
     * @return  string  The text in UTF-8.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function decode(string $bytes, int $codepage): string
    {
        // Plain ASCII is the same in all supported codepages
        if (!preg_match('/[\x80-\xFF]/', $bytes)) {
            return $bytes;
        }

        [$mbstring, $iconv] = self::ENCODINGS[$codepage] ?? self::ENCODINGS[1252];

        if ($mbstring !== null) {
            try {
                return mb_convert_encoding($bytes, 'UTF-8', $mbstring);
            } catch (\ValueError) {
                // Not supported by this mbstring version, try iconv
            }
        }

        if (\function_exists('iconv')) {
            $text = @iconv($iconv, 'UTF-8//IGNORE', $bytes);

            if ($text !== false) {
                return $text;
            }
        }

        return mb_convert_encoding($bytes, 'UTF-8', 'Windows-1252');
    }
}
