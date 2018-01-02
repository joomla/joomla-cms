<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;

Log::add('The layout joomla.tinymce.buttons is deprecated, use joomla.editors.buttons instead.', Log::WARNING, 'deprecated');
echo LayoutHelper::render('joomla.editors.buttons', $displayData);
