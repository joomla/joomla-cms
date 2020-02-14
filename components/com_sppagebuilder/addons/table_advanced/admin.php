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
		'type'=>'repeatable',
		'addon_name'=>'table_advanced',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TABLE_ADVANCED'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TABLE_ADVANCED_DESC'),
		'category'=>'Content',
		'attr' => false,
		'pro' => true,
	)
);
