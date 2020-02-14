<?php
/**
* @package Helix Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

function modChrome_sp_xhtml($module, $params, $attribs) {

	$moduleTag     = htmlspecialchars($params->get('module_tag', 'div'), ENT_QUOTES, 'UTF-8');
	$bootstrapSize = (int) $params->get('bootstrap_size', 0);
	$moduleClass   = $bootstrapSize !== 0 ? ' col-sm-' . $bootstrapSize : '';
	$headerTag     = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
	$headerClass   = htmlspecialchars($params->get('header_class', 'sp-module-title'), ENT_COMPAT, 'UTF-8');

	if ($module->content) {
		echo '<' . $moduleTag . ' class="sp-module ' . htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8') . $moduleClass . '">';
			if ($module->showtitle) {
				echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
			}
			echo '<div class="sp-module-content">';
			echo $module->content;
			echo '</div>';
		echo '</' . $moduleTag . '>';
	}
}