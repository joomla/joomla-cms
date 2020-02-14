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
		'addon_name'=>'sp_tweet',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_DESC'),
		'category'=>'Media',
		'attr'=>false,
		'pro'=>true,
	)
);
