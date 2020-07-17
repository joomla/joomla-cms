<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_multilangstatus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseInterface;

$multilanguageEnabled = Multilanguage::isEnabled($app, Factory::getContainer()->get(DatabaseInterface::class));

require ModuleHelper::getLayoutPath('mod_multilangstatus', $params->get('layout', 'default'));
