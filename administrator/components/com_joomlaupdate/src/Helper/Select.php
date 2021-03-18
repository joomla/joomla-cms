<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Joomla! update selection list helper.
 *
 * @since  2.5.4
 */
class Select
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
		$options[] = HTMLHelper::_('select.option', 'direct', Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD_DIRECT'));
		$options[] = HTMLHelper::_('select.option', 'hybrid', Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD_HYBRID'));
		$options[] = HTMLHelper::_('select.option', 'ftp', Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD_FTP'));

		return HTMLHelper::_('select.genericlist', $options, $name, 'class="custom-select"', 'value', 'text', $default, $id);
	}
}
