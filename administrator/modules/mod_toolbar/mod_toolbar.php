<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_toolbar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$toolbar = JToolbar::getInstance('toolbar')->render('toolbar');

require JModuleHelper::getLayoutPath('mod_toolbar', $params->get('layout', 'default'));
