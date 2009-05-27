<?php
/**
 * @version		$Id: mod_banners.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	mod_footer
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

$app		= &JFactory::getApplication();
$date		= &JFactory::getDate();
$cur_year	= $date->toFormat('%Y');
$csite_name	= $app->getCfg('sitename');

if (JString::strpos(JText :: _('FOOTER_LINE1'), '%date%')) {
	$line1 = ereg_replace('%date%', $cur_year, JText :: _('FOOTER_LINE1'));
}
else {
	$line1 = JText :: _('FOOTER_LINE1');
}

if (JString::strpos($line1, '%sitename%')) {
	$lineone = ereg_replace('%sitename%', $csite_name, $line1);
}
else {
	$lineone = $line1;
}

require JModuleHelper::getLayoutPath('mod_footer');
