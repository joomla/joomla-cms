<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   © 2006 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->name = JText::_('COM_CONFIG_TEXT_FILTER_SETTINGS');
$this->fieldsname = 'filters';
$this->description = JText::_('COM_CONFIG_TEXT_FILTERS_DESC');
echo JLayoutHelper::render('joomla.content.text_filters', $this);
