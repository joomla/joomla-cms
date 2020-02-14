<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

SpAddonsConfig::addonConfig(
	array(
		'type'=>'content',
		'addon_name'=>'sp_openstreetmap',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPENSTREETMAP'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPENSTREETMAP_DESC'),
		'category'=>'General',
		'attr'=> false,
		'pro'=> true,
	)
);
