<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->name = JText::_('COM_CONFIG_CACHE_SETTINGS');
$this->fieldsname = 'cache';
if (isset($this->data['cache_handler'])
	&& $this->data['cache_handler'] == 'memcache'
	|| $this->data['session_handler'] == 'memcache'
	|| $this->data['cache_handler'] == 'memcached'
	|| $this->data['session_handler'] == 'memcached'
)
{
	$this->fieldsname .= ',memcache';
}
echo JLayoutHelper::render('joomla.content.options_default', $this);
