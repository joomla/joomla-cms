<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

jimport('joomla.html.html');
jimport('joomla.form.fields.list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldAccessSection extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @access	public
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'AccessSection';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @access	protected
	 * @return	array		An array of JHtml options.
	 * @since	1.6
	 */
	protected function _getOptions()
	{
		// Get the user groups from the database.
		$db = &JFactory::getDBO();
		$db->setQuery(
			'SELECT `id` AS value, `title` AS text' .
			' FROM `#__access_sections`' .
			' ORDER BY `ordering`, `title` ASC'
		);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		return $options;
	}
}