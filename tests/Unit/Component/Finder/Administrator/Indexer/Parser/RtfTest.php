<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Finder\Administrator\Indexer\Parser;

use Joomla\Component\Finder\Administrator\Indexer\Parser\Rtf;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for \Joomla\Component\Finder\Administrator\Indexer\Parser\Rtf
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Rtf::class)]
class RtfTest extends UnitTestCase
{
    /**
     * Header of a typical RTF document with a font table, a colour table, a stylesheet and document information.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private const HEADER = '{\rtf1\ansi\ansicpg1252\deff0\deflang1033'
        . '{\fonttbl{\f0\froman\fcharset0 Times New Roman;}{\f1\fswiss\fcharset238 Arial CE;}{\f2\fswiss\fcharset204 Arial Cyr;}}'
        . '{\colortbl;\red0\green0\blue0;\red255\green0\blue0;}'
        . '{\stylesheet{\s0\ql Normal;}{\s1\b heading 1;}}'
        . '{\info{\title Document title}{\author Jane Doe}{\*\company ACME}}'
        . '{\*\generator Riched20 10.0.19041;}' . "\n";

    /**
     * Data for testParse
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataParse(): array
    {
        return [
            'plain text and paragraphs' => [
                '{\rtf1\ansi Hello\par World\line again}',
                "Hello\nWorld\nagain",
            ],
            'header groups are not text' => [
                self::HEADER . '\pard\plain Text}',
                'Text',
            ],
            'semicolons in text keep the text' => [
                '{\rtf1\ansi\uc1 \u8220?Dupin,\u8221? said I; \u8220?you are right.\u8221?\par}',
                "\u{201C}Dupin,\u{201D} said I; \u{201C}you are right.\u{201D}\n",
            ],
            'codepage characters' => [
                "{\\rtf1\\ansi\\ansicpg1252 Ant\\'e1rtida, caf\\'e9}",
                'Antártida, café',
            ],
            'codepage of the font' => [
                self::HEADER . "{\\f1 \\'e1rv\\'edzt\\'fbr\\'f5} {\\f2 \\'cf\\'f0\\'e8\\'e2\\'e5\\'f2} \\'e9}",
                'árvíztűrő Привет é',
            ],
            'document codepage' => [
                "{\\rtf1\\ansi\\ansicpg1251 \\'cf\\'f0\\'e8\\'e2\\'e5\\'f2}",
                'Привет',
            ],
            'unicode characters and their fallback' => [
                '{\rtf1\ansi Congress\u8212?was \uc2 na\u239\\\'ef\\\'efve \uc0 \u233 t\u-3913?}',
                "Congress\u{2014}was naïve ét\u{F0B7}?",
            ],
            'unicode character zero is not lost' => [
                '{\rtf1\ansi A\u48?B}',
                'A0B',
            ],
            'special characters as control words' => [
                '{\rtf1\ansi a\emdash b\endash c\ldblquote d\rdblquote \lquote e\rquote \bullet\tab f}',
                "a\u{2014}b\u{2013}c\u{201C}d\u{201D}\u{2018}e\u{2019}\u{2022} f",
            ],
            'control symbols' => [
                '{\rtf1\ansi a\{b\}c\\\\d non\~breaking non\_breaking op\-tional}',
                'a{b}c\d non breaking non-breaking optional',
            ],
            'nested pictures and binary data are removed' => [
                '{\rtf1\ansi before{\pict{\*\picprop{\sp{\sn shapeType}{\sv 75}}}\pngblip 89504e470d0a1a0a}after'
                    . '{\object\objemb{\*\objdata 0105000002}} \bin3 {}}end}',
                'beforeafter end',
            ],
            'unknown optional destinations are skipped' => [
                '{\rtf1\ansi {\*\unknowndestination hidden}visible}',
                'visible',
            ],
            'fields keep their result only' => [
                '{\rtf1\ansi Visit {\field{\*\fldinst HYPERLINK "https://www.joomla.org"}{\fldrslt joomla.org}}.}',
                'Visit joomla.org.',
            ],
            'line breaks in the source are not text' => [
                "{\\rtf1\\ansi one\r\ntwo\\\r\nthree}",
                "onetwo\nthree",
            ],
            'headers and footers are not text' => [
                '{\rtf1\ansi {\header Page header}{\footer Page footer}Body}',
                'Body',
            ],
            'unbalanced groups' => [
                '{\rtf1\ansi text}}} more',
                'text more',
            ],
            'empty input' => [
                '',
                '',
            ],
        ];
    }

    /**
     * Tests the parse method
     *
     * @param   string  $input     The RTF input
     * @param   string  $expected  The expected plain text
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataParse')]
    public function testParse(string $input, string $expected): void
    {
        $this->assertSame($expected, (new Rtf())->parse($input));
    }

    /**
     * Tests that long documents are parsed completely and not in chunks
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testParseLongDocument(): void
    {
        $paragraph = '\pard\plain \u8220?Paragraph with text; and a semicolon\u8221? {\b bold} and caf\\\'e9\par' . "\n";
        $input     = self::HEADER . str_repeat($paragraph, 2000) . '}';
        $output    = (new Rtf())->parse($input);

        $this->assertGreaterThan(64 * 1024, \strlen($input));
        $this->assertSame(2000, substr_count($output, "\u{201C}Paragraph with text; and a semicolon\u{201D} bold and café\n"));
        $this->assertStringNotContainsString('Times New Roman', $output);
        $this->assertStringNotContainsString('\\', $output);
    }
}
