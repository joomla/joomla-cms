<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JLog::add('The layout joomla.tinymce.buttons is deprecated, use joomla.editors.buttons instead.', JLog::WARNING, 'deprecated');
echo JLayoutHelper::render('joomla.editors.buttons', $displayData);
