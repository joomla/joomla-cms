<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.aurora
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * eg. To render a module mod_test in the submenu style, you would use the following include:
 * <jdoc:include type="module" name="test" style="submenu" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * two arguments.
 */

/*
 * Module chrome for rendering the module in a submenu
 */
function modChrome_no($module, &$params, &$attribs)
{
	if ($module->content)
	{
		echo $module->content;
	}
}

function modChrome_default($module, &$params, &$attribs)
{
	$modulePos	   = $module->position;
	$moduleTag     = $params->get('module_tag', 'div');
	$headerTag     = htmlspecialchars($params->get('header_tag', 'h4'));
	$headerClass   = htmlspecialchars($params->get('header_class', ''));

	if ($module->content)
	{
		echo '<' . $moduleTag . ' class="' . $modulePos . ' card' . htmlspecialchars($params->get('moduleclass_sfx')) . '">';
		if ($module->showtitle && $headerClass !== 'card-title')
		{
			echo '<' . $headerTag . ' class="card-header' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
		}
		echo '<div class="card-block">';
		if ($module->showtitle && $headerClass === 'card-title')
		{
			echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
		}
		echo $module->content;
		echo '</div>';
		echo '</' . $moduleTag . '>';
	}
}

function modChrome_cardGrey($module, &$params, &$attribs)
{
	$modulePos	   = $module->position;
	$moduleTag     = $params->get('module_tag', 'div');
	$headerTag     = htmlspecialchars($params->get('header_tag', 'h4'));
	$headerClass   = htmlspecialchars($params->get('header_class', ''));

	if ($module->content)
	{
		echo '<' . $moduleTag . ' class="' . $modulePos . ' card card-grey' . htmlspecialchars($params->get('moduleclass_sfx')) . '">';
		if ($module->showtitle && $headerClass !== 'card-title')
		{
			echo '<' . $headerTag . ' class="card-header' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
		}
		echo '<div class="card-block">';
		if ($module->showtitle && $headerClass === 'card-title')
		{
			echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
		}
		echo $module->content;
		echo '</div>';
		echo '</' . $moduleTag . '>';
	}
}
