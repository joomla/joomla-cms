<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

$this->name = Text::_('COM_CONFIG_SEO_METADATA_SETTINGS');
$this->fieldsname = 'seo_metadata';
$this->formclass = 'options-grid-form options-grid-form-full';
$this->description = Text::_('COM_CONFIG_SEO_METADATA_SETTINGS_DESCRIPTION');

echo LayoutHelper::render('joomla.content.options_default', $this);

// We don't use the description in any other options groups - remove it so the remaining groups don't use it
unset($this->description);
