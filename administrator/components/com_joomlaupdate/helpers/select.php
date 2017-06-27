<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! update selection list helper.
 *
 * @since  2.5.4
 */
class JoomlaupdateHelperSelect
{
	/**
	 * Returns an HTML select element with the different extraction modes
	 *
	 * @param   string  $default  The default value of the select element
	 * @param   string  $name     The name of the form field
	 * @param   string  $id       The id of the select field
	 *
	 * @return  string
	 *
	 * @since   2.5.4
	 */
	public static function getMethods($default = 'hybrid', $name = 'method', $id = 'extraction_method')
	{
		$options = array();
		$options[] = JHtml::_('select.option', 'direct', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD_DIRECT'));
		$options[] = JHtml::_('select.option', 'hybrid', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD_HYBRID'));
		$options[] = JHtml::_('select.option', 'ftp', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD_FTP'));

		return JHtml::_('select.genericlist', $options, $name, '', 'value', 'text', $default, $id);
	}
}
