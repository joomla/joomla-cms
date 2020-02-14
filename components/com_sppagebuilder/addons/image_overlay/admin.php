<?php
/**
* @package SP Page Builder
* @author JoomShaper https://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

SpAddonsConfig::addonConfig(
	array(
		'type'=>'content',
		'addon_name'=>'image_overlay',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_IMAGE_OVERLAY'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_IMAGE_OVERLAY_DESC'),
		'category'=>'Media',
		'attr' => false,
		'pro' => true,
	)
);
