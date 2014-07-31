<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Joomla! update selection list helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @since       2.5.4
 */
class JoomlaupdateHelperSelect
{
	/**
	 * Returns an HTML select element with the different extraction modes
	 * 
	 * @param   string  $default  The default value of the select element
	 * 
	 * @return  string
	 *
	 * @since   2.5.4
	 */
	public static function getMethods($default = 'direct')
	{
		$options = array();
		$options[] = JHtml::_('select.option', 'direct', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD_DIRECT'));
		$options[] = JHtml::_('select.option', 'ftp', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD_FTP'));

		return JHtml::_('select.genericlist', $options, 'method', '', 'value', 'text', $default, 'extraction_method');
	}
}
