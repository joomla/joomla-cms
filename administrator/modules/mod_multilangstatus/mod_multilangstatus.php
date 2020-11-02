<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_multilangstatus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Multilanguage;

// Don't display output if multilanguage functionality is not enabled.
if (!Multilanguage::isEnabled())
{
	return;
}

require ModuleHelper::getLayoutPath('mod_multilangstatus', $params->get('layout', 'default'));
