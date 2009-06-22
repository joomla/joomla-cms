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
		$options	= array();

		if ($published === '') {
			$published = null;
		}

		if (!empty($extension))
		{
			if ($published) {
				$options = JHtml::_('category.options', $extension, array('filter.published' => implode(',', $published)));
			}
			else {
				$options = JHtml::_('category.options', $extension);
			}
		}
		else
		{
			JError::raiseWarning(500, JText::_('JFramework_Form_Fields_Category_Error_extension_empty'));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);

		return $options;
	}
}