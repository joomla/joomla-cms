<?php

/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2019 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

SpAddonsConfig::addonConfig(
	array(
		'type'=>'content',
		'addon_name'=>'animated_heading',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_HEADING'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_HEADING_DESC'),
		'category'=>'General',
		'attr'=>false,
		'pro'=>true,
	)
);
