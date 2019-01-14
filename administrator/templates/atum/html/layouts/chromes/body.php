<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

$modulePos   = $module->position;
$moduleTag   = $params->get('module_tag', 'div');
$headerTag   = htmlspecialchars($params->get('header_tag', 'h4'));
$headerClass = htmlspecialchars($params->get('header_class', ''));

if ($module->content)
{
	echo '<' . $moduleTag . ' class="' . $modulePos . ' card ' . htmlspecialchars($params->get('moduleclass_sfx')) . '">';
	if ($module->showtitle && $headerClass !== 'card-title')
	{
		echo '<' . $headerTag . ' class="card-header' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
	}
	echo '<div class="card-body">';
	if ($module->showtitle && $headerClass === 'card-title')
	{
		echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
	}
	echo $module->content;
	echo '</div>';
	echo '</' . $moduleTag . '>';
}
