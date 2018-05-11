<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_toolbar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Toolbar\Toolbar;

$toolbar = Toolbar::getInstance('toolbar')->render('toolbar');

require ModuleHelper::getLayoutPath('mod_toolbar', $params->get('layout', 'default'));
