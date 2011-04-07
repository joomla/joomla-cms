<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	mod_custom
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

if ($params->def('prepare_content', 1))
{
	JPluginHelper::importPlugin('content');
	$module->content = JHtml::_('content.prepare', $module->content);
}

require JModuleHelper::getLayoutPath('mod_custom');
