<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.database.query');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldAccessLevel extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'AccessLevel';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$db		= &JFactory::getDbo();
		$query	= new JQuery;

		$query->select('a.id AS value, a.title AS text');
		$query->from('#__viewlevels AS a');
		$query->group('a.id');
		$query->order('a.ordering ASC');

		// Get the options.
		$db->setQuery((string) $query);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);

		return $options;
	}
}