<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$item = $displayData;

if ($item->language === '*')
{
	echo JText::alt('JALL', 'language');
}
elseif ($item->language_image)
{
	echo JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', '', null, true) . '&nbsp;' . htmlspecialchars($item->language_title, ENT_COMPAT, 'UTF-8');
}
elseif ($item->language_title)
{
	echo htmlspecialchars($item->language_title, ENT_COMPAT, 'UTF-8');
}
else
{
	echo JText::_('JUNDEFINED');
}
