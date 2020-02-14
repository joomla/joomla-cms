<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');

SpAddonsConfig::addonConfig(
	array(
		'type'=>'repeatable',
		'addon_name'=>'sp_carouselpro',
		'category'=>'Slider',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CAROUSEL_ADVANCED'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CAROUSEL_ADVANCED_DESC'),
		'attr'=>false,
		'pro'=>true,
	)
);
