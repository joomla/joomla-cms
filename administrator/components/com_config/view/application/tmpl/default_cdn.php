<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @todo Create a way of testing the CDN files exist before saving them in the configuration files to avoid breaking if a users types a URL incorrectly.
 */

defined('_JEXEC') or die;
 
$this->name = JText::_('COM_CONFIG_CDN_SETTINGS');
$this->fieldsname = 'cdn';
$this->description = JText::_('COM_CONFIG_CDN_DESC');
echo JLayoutHelper::render('joomla.content.options_default', $this);
