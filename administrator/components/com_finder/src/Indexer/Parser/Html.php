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
 * HTML Parser class for the Finder indexer package.
 *
 * @since  2.5
 */
class Html extends Parser
{
    /**
     * Method to parse input and extract the plain text. Because this method is
     * called from both inside and outside the indexer, it needs to be able to
     * batch out its parsing functionality to deal with the inefficiencies of
     * regular expressions. We will parse recursively in 2KB chunks.
     *
     * @param   string  $input  The input to parse.
     *
     * @return  string  The plain text input.
     *
     * @since   2.5
     */
    public function parse($input)
    {
        // Strip invalid UTF-8 characters.
        $oldSetting = ini_get('mbstring.substitute_character');
        ini_set('mbstring.substitute_character', 'none');
        $input = mb_convert_encoding($input, 'UTF-8', 'UTF-8');
        ini_set('mbstring.substitute_character', $oldSetting);

        // Remove anything between <head> and </head> tags.  Do this first
        // because there might be <script> or <style> tags nested inside.
        $input = $this->removeBlocks($input, '<head>', '</head>');

        // Convert <style> and <noscript> tags to <script> tags
        // so we can remove them efficiently.
        $search = [
            '<style', '</style',
            '<noscript', '</noscript',
        ];
        $replace = [
            '<script', '</script',
            '<script', '</script',
        ];
        $input = str_replace($search, $replace, $input);

        // Strip all script blocks.
        $input = $this->removeBlocks($input, '<script', '</script>');

        // Decode HTML entities.
        $input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');

        // Convert entities equivalent to spaces to actual spaces.
        $input = str_replace(['&nbsp;', '&#160;'], ' ', $input);

        // Add a space before both the OPEN and CLOSE tags of BLOCK and LINE BREAKING elements,
        // e.g. 'all<h1><em>m</em>obile  List</h1>' will become 'all mobile  List'
        $input = preg_replace('/(<|<\/)(' .
            'address|article|aside|blockquote|br|canvas|dd|div|dl|dt|' .
            'fieldset|figcaption|figure|footer|form|h1|h2|h3|h4|h5|h6|header|hgroup|hr|li|' .
            'main|nav|noscript|ol|output|p|pre|section|table|tfoot|ul|video' .
            ')\b/i', ' $1$2', $input);

        // Strip HTML tags.
        $input = strip_tags($input);

        return parent::parse($input);
    }

    /**
     * Method to process HTML input and extract the plain text.
     *
     * @param   string  $input  The input to process.
     *
     * @return  string  The plain text input.
     *
     * @since   2.5
     */
    protected function process($input)
    {
        // Replace any amount of white space with a single space.
        return preg_replace('#\s+#u', ' ', $input);
    }

    /**
     * Method to remove blocks of text between a start and an end tag.
     * Each block removed is effectively replaced by a single space.
     *
     * Note: The start tag and the end tag must be different.
     * Note: Blocks must not be nested.
     * Note: This method will function correctly with multi-byte strings.
     *
     * @param   string  $input     String to be processed.
     * @param   string  $startTag  String representing the start tag.
     * @param   string  $endTag    String representing the end tag.
     *
     * @return  string with blocks removed.
     *
     * @since   3.4
     */
    private function removeBlocks($input, $startTag, $endTag)
    {
        $return         = '';
        $offset         = 0;
        $startTagLength = strlen($startTag);
        $endTagLength   = strlen($endTag);

        // Find the first start tag.
        $start = stripos($input, $startTag);

        // If no start tags were found, return the string unchanged.
        if ($start === false) {
            return $input;
        }

        // Look for all blocks defined by the start and end tags.
        while ($start !== false) {
            // Accumulate the substring up to the start tag.
            $return .= substr($input, $offset, $start - $offset) . ' ';

            // Look for an end tag corresponding to the start tag.
            $end = stripos($input, $endTag, $start + $startTagLength);

            // If no corresponding end tag, leave the string alone.
            if ($end === false) {
                // Fix the offset so part of the string is not duplicated.
                $offset = $start;
                break;
            }

            // Advance the start position.
            $offset = $end + $endTagLength;

            // Look for the next start tag and loop.
            $start = stripos($input, $startTag, $offset);
        }

        // Add in the final substring after the last end tag.
        $return .= substr($input, $offset);

        return $return;
    }
}
