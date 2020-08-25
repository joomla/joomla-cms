<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLog::add('The layout joomla.content.info_block.block is deprecated, use joomla.content.info_block instead.', JLog::WARNING, 'deprecated');
echo JLayoutHelper::render('joomla.content.info_block', $displayData);
