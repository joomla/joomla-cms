<?php
/**
 * @version		$Id: editors.php 11548 2009-01-29 12:42:29Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldEditors extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Editors';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		// compile list of the editors
		$query	= 'SELECT element AS value, name AS text'
				. ' FROM #__plugins'
				. ' WHERE folder = "editors"'
				. ' AND published = 1'
				. ' ORDER BY ordering, name';
		$db = & JFactory::getDbo();
		$db->setQuery($query);

		$editors = $db->loadObjectList();

		// @todo: Check for an error msg.

		$options = array_merge(parent::_getOptions(), $editors);

		return $options;
	}
}