<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.emailcloak
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\EmailCloak\Extension;

use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Email cloak plugin class.
 *
 * @since  1.5
 */
final class EmailCloak extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    /**
     * Plugin that cloaks all emails in content from spambots via Javascript.
     *
     * @param   ContentPrepareEvent  $event  Event instance
     *
     * @return  void
     */
    public function onContentPrepare(ContentPrepareEvent $event)
    {
        // Don't run if in the API Application
        // Don't run this plugin when the content is being indexed
        if ($this->getApplication()->isClient('api') || $event->getContext() === 'com_finder.indexer') {
            return;
        }

        // Get content item
        $item = $event->getItem();

        // If the item does not have a text property there is nothing to do
        if (!isset($item->text)) {
            return;
        }

        $text = $this->cloak($item->text);

        if ($text) {
            $item->text = $text;
        }
    }

    /**
     * Generate a search pattern based on link and text.
     *
     * @param   string  $link  The target of an email link.
     * @param   string  $text  The text enclosed by the link.
     *
     * @return  string  A regular expression that matches a link containing the parameters.
     */
    private function getPattern($link, $text)
    {
        $pattern = '~(?:<a ([^>]*)href\s*=\s*"mailto:' . $link . '"([^>]*))>' . $text . '</a>~i';

        return $pattern;
    }

    /**
     * Cloak all emails in text from spambots via Javascript.
     *
     * @param   string  $text    The string to be cloaked.
     *
     * @return  string
     */
    private function cloak($text)
    {
        /*
         * Check for presence of {emailcloak=off} which is explicits disables this
         * bot for the item.
         */
        if (StringHelper::strpos($text, '{emailcloak=off}') !== false) {
            return StringHelper::str_ireplace('{emailcloak=off}', '', $text);
        }

        // Simple performance check to determine whether bot should process further.
        if (StringHelper::strpos($text, '@') === false) {
            return '';
        }

        $mode = (int) $this->params->def('mode', 1);
        $mode = $mode === 1;

        // Example: any@example.org
        $searchEmail = '([\w\.\'\-\+]+\@(?:[a-z0-9\.\-]+\.)+(?:[a-zA-Z0-9\-]{2,24}))';

        // Example: any@example.org?subject=anyText
        $searchEmailLink = $searchEmail . '([?&][\x20-\x7f][^"<>]+)';

        // Any Text
        $searchText = '((?:[\x20-\x7f]|[\xA1-\xFF]|[\xC2-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF4][\x80-\xBF]{3})[^<>]+)';

        // Any Image link
        $searchImage = '(<img[^>]+>)';

        // Any Text with <span or <strong
        $searchTextSpan = '(<span[^>]+>|<span>|<strong>|<strong><span[^>]+>|<strong><span>)' . $searchText . '(</span>|</strong>|</span></strong>)';

        // Any address with <span or <strong
        $searchEmailSpan = '(<span[^>]+>|<span>|<strong>|<strong><span[^>]+>|<strong><span>)' . $searchEmail . '(</span>|</strong>|</span></strong>)';

        /*
         * Search and fix derivatives of link code <a href="http://mce_host/ourdirectory/email@example.org"
         * >email@example.org</a>. This happens when inserting an email in TinyMCE, cancelling its suggestion to add
         * the mailto: prefix...
         */
        $pattern = $this->getPattern($searchEmail, $searchEmail);
        $pattern = str_replace('"mailto:', '"([\x20-\x7f][^<>]+/)', $pattern);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[3][0];
            $mailText      = $regs[5][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 1, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search and fix derivatives of link code <a href="http://mce_host/ourdirectory/email@example.org"
         * >anytext</a>. This happens when inserting an email in TinyMCE, cancelling its suggestion to add
         * the mailto: prefix...
         */
        $pattern = $this->getPattern($searchEmail, $searchText);
        $pattern = str_replace('"mailto:', '"([\x20-\x7f][^<>]+/)', $pattern);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[3][0];
            $mailText      = $regs[5][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@example.org"
         * >email@example.org</a>
         */
        $pattern = $this->getPattern($searchEmail, $searchEmail);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0];
            $mailText      = $regs[4][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[3][0];

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 1, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@amail.com"
         * ><anyspan >email@amail.com</anyspan></a>
         */
        $pattern = $this->getPattern($searchEmail, $searchEmailSpan);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0];
            $mailText      = $regs[4][0] . $regs[5][0] . $regs[6][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[3][0];

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 1, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@amail.com">
         * <anyspan >anytext</anyspan></a>
         */
        $pattern = $this->getPattern($searchEmail, $searchTextSpan);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0];
            $mailText      = $regs[4][0] . $regs[5][0] . $regs[6][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[3][0];

            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@example.org">
         * anytext</a>
         */
        $pattern = $this->getPattern($searchEmail, $searchText);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0];
            $mailText      = $regs[4][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[3][0];

            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@example.org">
         * <img anything></a>
         */
        $pattern = $this->getPattern($searchEmail, $searchImage);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0];
            $mailText      = $regs[4][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[3][0];

            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@example.org">
         * <img anything>email@example.org</a>
         */
        $pattern = $this->getPattern($searchEmail, $searchImage . $searchEmail);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0];
            $mailText      = $regs[4][0] . $regs[5][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[3][0];

            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 1, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@example.org">
         * <img anything>any text</a>
         */
        $pattern = $this->getPattern($searchEmail, $searchImage . $searchText);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0];
            $mailText      = $regs[4][0] . $regs[5][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[3][0];

            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@example.org?
         * subject=Text">email@example.org</a>
         */
        $pattern = $this->getPattern($searchEmailLink, $searchEmail);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0] . $regs[3][0];
            $mailText      = $regs[5][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            // Needed for handling of Body parameter
            $mail = str_replace('&amp;', '&', $mail);

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 1, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@example.org?
         * subject=Text">anytext</a>
         */
        $pattern = $this->getPattern($searchEmailLink, $searchText);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0] . $regs[3][0];
            $mailText      = $regs[5][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            // Needed for handling of Body parameter
            $mail = str_replace('&amp;', '&', $mail);

            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@amail.com?subject= Text"
         * ><anyspan >email@amail.com</anyspan></a>
         */
        $pattern = $this->getPattern($searchEmailLink, $searchEmailSpan);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0] . $regs[3][0];
            $mailText      = $regs[5][0] . $regs[6][0] . $regs[7][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 1, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code <a href="mailto:email@amail.com?subject= Text">
         * <anyspan >anytext</anyspan></a>
         */
        $pattern = $this->getPattern($searchEmailLink, $searchTextSpan);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0] . $regs[3][0];
            $mailText      = $regs[5][0] . $regs[6][0] . $regs[7][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code
         * <a href="mailto:email@amail.com?subject=Text"><img anything></a>
         */
        $pattern = $this->getPattern($searchEmailLink, $searchImage);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0] . $regs[3][0];
            $mailText      = $regs[5][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            // Needed for handling of Body parameter
            $mail = str_replace('&amp;', '&', $mail);

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code
         * <a href="mailto:email@amail.com?subject=Text"><img anything>email@amail.com</a>
         */
        $pattern = $this->getPattern($searchEmailLink, $searchImage . $searchEmail);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0] . $regs[3][0];
            $mailText      = $regs[5][0] . $regs[6][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            // Needed for handling of Body parameter
            $mail = str_replace('&amp;', '&', $mail);

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 1, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for derivatives of link code
         * <a href="mailto:email@amail.com?subject=Text"><img anything>any text</a>
         */
        $pattern = $this->getPattern($searchEmailLink, $searchImage . $searchText);

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail          = $regs[2][0] . $regs[3][0];
            $mailText      = $regs[5][0] . $regs[6][0];
            $attribsBefore = $regs[1][0];
            $attribsAfter  = $regs[4][0];

            // Needed for handling of Body parameter
            $mail = str_replace('&amp;', '&', $mail);

            // Check to see if mail text is different from mail addy
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mailText, 0, $attribsBefore, $attribsAfter);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($regs[0][0]));
        }

        /*
         * Search for plain text email addresses, such as email@example.org but within HTML tags:
         * <img src="..." title="email@example.org"> or <input type="text" placeholder="email@example.org">
         * The '<[^<]*>(*SKIP)(*F)|' trick is used to exclude this kind of occurrences
         */
        $pattern = '~<[^<]*(?<!\/)>(*SKIP)(*F)|<[^>]+?(\w*=\"' . $searchEmail . '\")[^>]*\/>~i';

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail        = $regs[0][0];
            $replacement = HTMLHelper::_('email.cloak', $mail, 0, $mail);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($mail));
        }

        /*
         * Search for plain text email addresses, such as email@example.org but within HTML attributes:
         * <a title="email@example.org" href="#">email</a> or <li title="email@example.org">email</li>
         */
        $pattern = '(<[^>]+?(\w*=\"' . $searchEmail . '")[^>]*>[^<]+<[^<]+>)';

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail        = $regs[0][0];
            $replacement =  HTMLHelper::_('email.cloak', $mail, 0, $mail);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[0][1], \strlen($mail));
        }

        /*
        * Search for plain text email addresses, such as email@example.org but not within HTML tags:
        * <p>email@example.org</p>
        * The '<[^<]*>(*SKIP)(*F)|' trick is used to exclude this kind of occurrences
        * The '<[^<]*(?<!\/(?:src))>(*SKIP)(*F)|' exclude image files with @ in filename
        */

        $pattern = '~<[^<]*(?<!\/(?:src))>(*SKIP)(*F)|' . $searchEmail . '~i';

        while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $mail        = $regs[1][0];
            $replacement = HTMLHelper::_('email.cloak', $mail, $mode, $mail);

            // Replace the found address with the js cloaked email
            $text = substr_replace($text, $replacement, $regs[1][1], \strlen($mail));
        }

        return $text;
    }
}
