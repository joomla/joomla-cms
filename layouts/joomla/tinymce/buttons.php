<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   © 2013 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLog::add('The layout joomla.tinymce.buttons is deprecated, use joomla.editors.buttons instead.', JLog::WARNING, 'deprecated');
echo JLayoutHelper::render('joomla.editors.buttons', $displayData);
