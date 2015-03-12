<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$displayData = $this->getDisplayData('COM_CONFIG_PERMISSION_SETTINGS', 'permissions');
$displayData->formclass = 'form-vertical';
$displayData->showlabel = false;
echo JLayoutHelper::render('joomla.content.options_default', $displayData);
