<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$item = $displayData;

if ($item->language == '')
{
	echo JText::_('JUNDEFINED');
}
elseif ($item->language == '*')
{
	echo JText::alt('JALL', 'language');
}
elseif ($item->language_image)
{
	echo JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, null, true) . '&nbsp;' . htmlspecialchars($item->language_title, ENT_COMPAT, 'UTF-8');
}
elseif ($item->language_title)
{
	echo htmlspecialchars($item->language_title, ENT_COMPAT, 'UTF-8');
}
else
{
	echo JText::_('JUNDEFINED');
}
