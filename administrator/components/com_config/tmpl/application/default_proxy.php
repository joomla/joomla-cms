<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

$this->name = Text::_('COM_CONFIG_PROXY_SETTINGS');
$this->fieldsname = 'proxy';
echo LayoutHelper::render('joomla.content.options_default', $this);
