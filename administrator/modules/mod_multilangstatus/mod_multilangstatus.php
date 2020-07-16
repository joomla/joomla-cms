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
use Joomla\Component\Languages\Administrator\Helper\MultilangstatusHelper;

$mutilanguageEnabled = Multilanguage::isEnabled() && MultilangstatusHelper::getLangswitchers();

require ModuleHelper::getLayoutPath('mod_multilangstatus', $params->get('layout', 'default'));
