<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 */
defined('JPATH_PLATFORM') or die;

$this->name = JText::_('WF_PROFILES_DETAILS');
$this->fieldsname = 'setup';
echo JLayoutHelper::render('joomla.content.options_default', $this);

$this->name = JText::_('WF_PROFILES_ASSIGNMENT');
$this->fieldsname = 'assignment';
echo JLayoutHelper::render('joomla.content.options_default', $this);