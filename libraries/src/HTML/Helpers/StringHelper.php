<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper as FrameworkStringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML helper class for rendering manipulated strings.
 *
 * @since  1.6
 */
abstract class StringHelper
{
    /**
     * Truncates text blocks over the specified character limit and closes
     * all open HTML tags. The method will optionally not truncate an individual
     * word, it will find the first space that is within the limit and
     * truncate at that point. This method is UTF-8 safe.
     *
     * @param   string   $text       The text to truncate.
     * @param   integer  $length     The maximum length of the text.
     * @param   boolean  $noSplit    Don't split a word if that is where the cutoff occurs (default: true).
     * @param   boolean  $allowHtml  Allow HTML tags in the output, and close any open tags (default: true).
     *
     * @return  string   The truncated text.
     *
     * @since   1.6
     */
    public static function truncate($text, $length = 0, $noSplit = true, $allowHtml = true)
    {
        // Assume a lone open tag is invalid HTML
        if ($length === 1 && $text[0] === '<') {
            return '...';
        }

        // Check if HTML tags are allowed.
        if (!$allowHtml) {
            // Decode entities
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

            // Deal with spacing issues in the input.
            $text = str_replace('>', '> ', $text);
            $text = str_replace(['&nbsp;', '&#160;'], ' ', $text);
            $text = FrameworkStringHelper::trim(preg_replace('#\s+#mui', ' ', $text));

            // Strip tags from the input.
            $text = strip_tags($text);

            // Remove remaining extra spaces.
            $text = str_replace('&nbsp;', ' ', $text);
            $text = FrameworkStringHelper::trim(preg_replace('#\s+#mui', ' ', $text));
        }

        // Whether or not allowing HTML, truncate the item text if it is too long.
        if ($length > 0 && FrameworkStringHelper::strlen($text) > $length) {
            $tmp = trim(FrameworkStringHelper::substr($text, 0, $length));

            if ($tmp[0] === '<' && !str_contains($tmp, '>')) {
                return '...';
            }

            // $noSplit true means that we do not allow splitting of words.
            if ($noSplit) {
                // Find the position of the last space within the allowed length.
                $offset = FrameworkStringHelper::strrpos($tmp, ' ');
                $tmp    = FrameworkStringHelper::substr($tmp, 0, $offset + 1);

                // If there are no spaces and the string is longer than the maximum
                // we need to just use the ellipsis. In that case we are done.
                if ($offset === false && \strlen($text) > $length) {
                    return '...';
                }

                if (FrameworkStringHelper::strlen($tmp) > $length - 3) {
                    $tmp = trim(FrameworkStringHelper::substr($tmp, 0, FrameworkStringHelper::strrpos($tmp, ' ')));
                }
            }

            if ($allowHtml) {
                // Put all opened tags into an array
                preg_match_all("#<([a-z][a-z0-9]*)\b.*?(?!/)>#i", $tmp, $result);
                $openedTags = $result[1];

                // Some tags self close so they do not need a separate close tag.
                $openedTags = array_diff($openedTags, ['img', 'hr', 'br']);
                $openedTags = array_values($openedTags);

                // Put all closed tags into an array
                preg_match_all("#</([a-z][a-z0-9]*)\b(?:[^>]*?)>#iU", $tmp, $result);
                $closedTags = $result[1];

                // Not all tags are closed so trim the text and finish.
                if (\count($closedTags) !== \count($openedTags)) {
                    // Closing tags need to be in the reverse order of opening tags.
                    $openedTags = array_reverse($openedTags);

                    // Close tags
                    foreach ($openedTags as $openedTag) {
                        if (!\in_array($openedTag, $closedTags)) {
                            $tmp .= '</' . $openedTag . '>';
                        } else {
                            unset($closedTags[array_search($openedTag, $closedTags)]);
                        }
                    }
                }

                // Check if we are within a tag
                if (FrameworkStringHelper::strrpos($tmp, '<') > FrameworkStringHelper::strrpos($tmp, '>')) {
                    $offset = FrameworkStringHelper::strrpos($tmp, '<');
                    $tmp    = FrameworkStringHelper::trim(FrameworkStringHelper::substr($tmp, 0, $offset));
                }
            }

            if ($tmp === false || \strlen($text) > \strlen($tmp)) {
                $text = trim($tmp) . '...';
            }
        }

        // Clean up any internal spaces created by the processing.
        $text = str_replace(' </', '</', $text);
        $text = str_replace(' ...', '...', $text);

        return $text;
    }

    /**
     * Method to extend the truncate method to more complex situations
     *
     * The goal is to get the proper length plain text string with as much of
     * the html intact as possible with all tags properly closed.
     *
     * @param   string   $html       The content of the introtext to be truncated
     * @param   integer  $maxLength  The maximum number of characters to render
     * @param   boolean  $noSplit    Don't split a word if that is where the cutoff occurs (default: true).
     *
     * @return  string  The truncated string. If the string is truncated an ellipsis
     *                  (...) will be appended.
     *
     * @note    If a maximum length of 3 or less is selected and the text has more than
     *          that number of characters an ellipsis will be displayed.
     *          This method will not create valid HTML from malformed HTML.
     *
     * @since   3.1
     */
    public static function truncateComplex($html, $maxLength = 0, $noSplit = true)
    {
        $baseLength = \strlen($html);

        // Early return for trivial cases
        if ($maxLength === 0 || $baseLength <= $maxLength) {
            return $html;
        }

        // Special case: very short cutoff, plain text.
        if ($maxLength <= 3 && $html[0] !== '<' && !str_contains(substr($html, 0, max(0, $maxLength - 1)), '<')) {
            return '...';
        }

        // Special case: string starts with a tag and maxLength is 1
        if ($maxLength === 1 && $html[0] === '<') {
            $endTagPos = strpos($html, '>');
            if ($endTagPos === false) {
                return '...';
            }
            $tag = substr($html, 1, $endTagPos - 1);
            return substr($html, 0, $endTagPos + 1) . "</$tag>...";
        }

        // Get a plain text truncated string
        $ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit, false);

        if ($ptString === '') {
            return $html;
        }
        if (!str_ends_with($ptString, '...')) {
            return $html;
        }
        if ($ptString === '...') {
            $stripped = substr(strip_tags($html), 0, $maxLength);
            $ptString = HTMLHelper::_('string.truncate', $stripped, $maxLength, $noSplit, false);
        }
        $ptString = rtrim($ptString, '.');

        while ($maxLength <= $baseLength) {
            $htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit, true);

            if ($htmlString === '...' && \strlen($ptString) + 3 > $maxLength) {
                return '...';
            }

            $htmlString = rtrim($htmlString, '.');

            // Get the plain text version of the truncated HTML string
            $htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit, false);
            $htmlStringToPtString = rtrim($htmlStringToPtString, '.');

            // If plain text matches, we're done
            if ($ptString === $htmlStringToPtString) {
                // Remove whitespace, non-breaking spaces, and trailing tags before the ellipsis
                $htmlString = preg_replace('/(&nbsp;|\s)+(<\/[^>]+>)?$/u', '', $htmlString);

                // If it ends with a closing tag, try to inject the ellipsis before the last closing tag
                if (preg_match('/(<\/[^>]+>)$/', $htmlString, $matches)) {
                    return preg_replace('/(<\/[^>]+>)$/', '...$1', $htmlString);
                }
                return $htmlString . '...';
            }

            // Adjust length for HTML tags
            $diffLength = \strlen($ptString) - \strlen($htmlStringToPtString);
            if ($diffLength <= 0) {
                // Remove whitespace, non-breaking spaces, and trailing tags before the ellipsis
                $htmlString = preg_replace('/(&nbsp;|\s)+(<\/[^>]+>)?$/u', '', $htmlString);

                // If it ends with a closing tag, inject the ellipsis before the last closing tag
                if (preg_match('/(<\/[^>]+>)$/', $htmlString, $matches)) {
                    return preg_replace('/(<\/[^>]+>)$/', '...$1', $htmlString);
                }
                return $htmlString . '...';
            }
            $maxLength += $diffLength;
        }

        return '';
    }

    /**
     * Abridges text strings over the specified character limit. The
     * behavior will insert an ellipsis into the text replacing a section
     * of variable size to ensure the string does not exceed the defined
     * maximum length. This method is UTF-8 safe.
     *
     * For example, it transforms "Really long title" to "Really...title".
     *
     * Note that this method does not scan for HTML tags so will potentially break them.
     *
     * @param   string   $text    The text to abridge.
     * @param   integer  $length  The maximum length of the text (default is 50).
     * @param   integer  $intro   The maximum length of the intro text (default is 30).
     *
     * @return  string   The abridged text.
     *
     * @since   1.6
     */
    public static function abridge($text, $length = 50, $intro = 30)
    {
        // Abridge the item text if it is too long.
        if (FrameworkStringHelper::strlen($text) > $length) {
            // Determine the remaining text length.
            $remainder = $length - ($intro + 3);

            // Extract the beginning and ending text sections.
            $beg = FrameworkStringHelper::substr($text, 0, $intro);
            $end = FrameworkStringHelper::substr($text, FrameworkStringHelper::strlen($text) - $remainder);

            // Build the resulting string.
            $text = $beg . '...' . $end;
        }

        return $text;
    }
}
