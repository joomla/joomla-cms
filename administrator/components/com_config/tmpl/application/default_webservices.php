<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

/** @var \Joomla\Component\Config\Administrator\View\Application\HtmlView $this */

$this->name = Text::_('COM_CONFIG_WEBSERVICES_SETTINGS');
$this->description = '';
$this->fieldsname = 'webservices';
$this->formclass = 'options-form';

echo LayoutHelper::render('joomla.content.options_default', $this);
