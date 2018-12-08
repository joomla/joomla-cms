<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JLog::add('The layout joomla.content.info_block.block is deprecated, use joomla.content.info_block instead.', JLog::WARNING, 'deprecated');
echo JLayoutHelper::render('joomla.content.info_block', $displayData);
