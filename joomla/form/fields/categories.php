<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Supports an HTML select list of categories
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldCategories extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Categories';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$db			= &JFactory::getDbo();
		$extension	= $this->_element->attributes('extension');
		$published	= $this->_element->attributes('published');
		$allowNone	= $this->_element->attributes('allow_none');

		if ($published === '') {
			$published = null;
		}

		if (!empty($extension)) {
			$db->setQuery(
				'SELECT c.id AS value, c.title AS text'.
				' FROM #__categories AS c'.
				' WHERE c.extension = '.$db->Quote($extension).
				($published !== null ? ' AND published = '.(int) $published : '').
				//' AND c.access IN ('.implode(',', $user->authorisedLevels($action)).')'.
				' GROUP BY c.id ORDER BY c.lft'
			);
		}
		else {
			JError::raiseWarning(500, JText::_('JFramework_Form_Fields_Category_Error_extension_empty'));
		}


		$options = $db->loadObjectList();

		// Check for an error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return false;
		}

		if (is_array($options)) {
			$options = array_merge(parent::_getOptions(), $options);
		}

		return $options;
	}
}