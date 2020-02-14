<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

SpAddonsConfig::addonConfig(
        array(
            'type' => 'repeatable',
            'addon_name' => 'sp_icons_group',
            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_ICONS_GROUP'),
            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_ICONS_GROUP_DESC'),
            'category' => 'Media',
            'attr'=>false,
		    'pro'=>true,
        )
);
