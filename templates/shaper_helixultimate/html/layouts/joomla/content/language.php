<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$item = $displayData;

if ($item->language === '*')
{
	echo Text::alt('JALL', 'language');
}
elseif ($item->language_image)
{
	echo HTMLHelper::_('image', 'mod_languages/' . $item->language_image . '.gif', '', null, true) . '&nbsp;' . htmlspecialchars($item->language_title, ENT_COMPAT, 'UTF-8');
}
elseif ($item->language_title)
{
	echo htmlspecialchars($item->language_title, ENT_COMPAT, 'UTF-8');
}
else
{
	echo Text::_('JUNDEFINED');
}
