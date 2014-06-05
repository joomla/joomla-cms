<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_footer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app		= JFactory::getApplication();
$date		= JFactory::getDate();
$cur_year	= JHtml::_('date', $date, 'Y');
$csite_name	= $app->get('sitename');

if (is_int(JString::strpos(JText :: _('MOD_FOOTER_LINE1'), '%date%')))
{
	$line1 = str_replace('%date%', $cur_year, JText :: _('MOD_FOOTER_LINE1'));
}
else
{
	$line1 = JText :: _('MOD_FOOTER_LINE1');
}

if (is_int(JString::strpos($line1, '%sitename%')))
{
	$lineone = str_replace('%sitename%', $csite_name, $line1);
}
else
{
	$lineone = $line1;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_footer', $params->get('layout', 'default'));
