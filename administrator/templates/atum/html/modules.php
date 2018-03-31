<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
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
 * eg.  To render a module mod_test in the submenu style, you would use the following include:
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
function modChrome_title($module, &$params, &$attribs)
{
	if ($module->content)
	{
		echo "<div class=\"card-header\"><h6>" . $module->title . "</h6></div>";
		echo $module->content;
	}
}

function modChrome_no($module, &$params, &$attribs)
{
	if ($module->content)
	{
		echo $module->content;
	}
}

function modChrome_well($module, &$params, &$attribs)
{
	if ($module->content)
	{
		// Permission checks
		$user           = JFactory::getUser();
		$canEdit	    = $user->authorise('core.edit', 'com_modules.module.' . $module->id);

		$moduleTag      = $params->get('module_tag', 'div');
		$bootstrapSize  = (int) $params->get('bootstrap_size', 6);
		$moduleClass    = ($bootstrapSize) ? 'col-md-' . $bootstrapSize : 'col-md-12';
		$headerTag      = htmlspecialchars($params->get('header_tag', 'h2'));
		$moduleClassSfx = $params->get('moduleclass_sfx', '');

		// Temporarily store header class in variable
		$headerClass    = $params->get('header_class');
		$headerClass    = ($headerClass) ? ' ' . htmlspecialchars($headerClass) : '';

		echo '<div class="' . $moduleClass . '">';
		echo '<' . $moduleTag . ' class="card mb-3' . $moduleClassSfx . '">';
		echo '<div class="card-body">';

			if ($canEdit)
			{
				echo '<div class="module-actions">';
				echo '<a href="' . JRoute::_('index.php?option=com_modules&task=module.edit&id=' . (int) $module->id) 
					. '"><span class="fa fa-cog"><span class="sr-only">' . JText::_('JACTION_EDIT') . " " . $module->title . '</span></span></a>';
				echo '</div>';
			}

			if ($module->showtitle)
			{
				echo '<h2 class="card-title nav-header' . $headerClass . '">' . $module->title . '</h2>';
			}

			echo $module->content;

		echo '</div>';
		echo '</' . $moduleTag . '>';
		echo '</div>';
	}
}
