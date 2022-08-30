<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$textClass = ($params->get('display_text', 1) ? '' : 'class="visually-hidden"');

$linkText = '<span class="icon-feed m-1" aria-hidden="true"></span>';
$linkText .= '<span ' . $textClass . '>' . (!empty($text) ? $text : Text::_('MOD_SYNDICATE_DEFAULT_FEED_ENTRIES')) . '</span>';

$attribs = [
    'class' => 'mod-syndicate syndicate-module'
];

echo HTMLHelper::_('link', $link, $linkText, $attribs);
