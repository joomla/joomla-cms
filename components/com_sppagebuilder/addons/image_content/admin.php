<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

SpAddonsConfig::addonConfig(
array(
	'type'=>'content',
	'addon_name'=>'sp_image_content',
	'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_IMAGE_CONTENT'),
	'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_IMAGE_CONTENT_DESC'),
	'category'=>'Content',
	'attr'=>false,
	'pro'=>true,
	)
);
