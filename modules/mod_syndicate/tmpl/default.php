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

$text = '<span class="icon-feed mr-1" aria-hidden="true"></span>'
	. (!empty($text) ? $text :  Text::_('MOD_SYNDICATE_DEFAULT_FEED_ENTRIES'))
	. ($params->get('display_text', 1) ? '' : 'class="visually-hidden"');

$attribs = [
	'class' => 'mod-syndicate syndicate-module'
];

echo HTMLHelper::_('link', $link, $text, $attribs);
