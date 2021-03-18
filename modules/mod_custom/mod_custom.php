<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_custom
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

if ($params->def('prepare_content', 1))
{
	PluginHelper::importPlugin('content');
	$module->content = HTMLHelper::_('content.prepare', $module->content, '', 'mod_custom.content');
}

require ModuleHelper::getLayoutPath('mod_custom', $params->get('layout', 'default'));
