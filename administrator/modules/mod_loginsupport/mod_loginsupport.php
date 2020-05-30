<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logsupport
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;

if ($params->get('automatic_title'))
{
	$module->title = Text::_('MOD_LOGINSUPPORT_TITLE');
}

require ModuleHelper::getLayoutPath('mod_loginsupport', $params->get('layout', 'default'));
