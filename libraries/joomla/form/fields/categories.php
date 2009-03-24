<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

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
		$db			= &JFactory::getDBO();
		$extension	= $this->_element->attributes('extension');
		$published	= $this->_element->attributes('published');
		$allowNone	= $this->_element->attributes('allow_none');

		if ($published === '') {
			$published = null;
		}

		if ($extension == 'content') {
			// This might get a conflict with the dynamic translation - TODO: search for better solution
			$db->setQuery(
				'SELECT c.id AS value, CONCAT_WS("/",s.title, c.title) AS text' .
				' FROM #__categories AS c' .
				' LEFT JOIN #__sections AS s ON s.id=c.section' .
				' WHERE s.scope = '.$db->Quote($section).
				($published !== null ? ' AND published = '.(int) $published : '').
				' ORDER BY s.title, c.title'
			);
		}
		else if (!empty($section))
		{
			$db->setQuery(
				'SELECT c.id AS value, c.title As text' .
				' FROM #__categories AS c' .
				' WHERE c.section = '.$db->Quote($section).
				($published !== null ? ' AND published = '.(int) $published : '').
				' ORDER BY c.title'
			);
		}
		else {
			JError::raiseWarning(500, JText::_('JFramework_Form_Fields_Category_Error_section_empty'));
		}

		try {
			$options = $db->loadObjectList();
		}
		catch(JException $e)
		{
			$options = array();
			if ($db->getErrorNum()) {
			}
		}

		$options	= array_merge(
						parent::_getOptions(),
						$options
					);

		return $options;
	}
}