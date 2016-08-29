<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.createmenulink
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldMenuOrdering extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since   __DEPLOY_VERSION__
	 */
	protected $type = 'MenuOrdering';

	/**
	 * Method to get the list of siblings in a menu.
	 * The method requires that parent be set.
	 *
	 * @return  array  The field option objects or false if the parent field has not been set
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$options = array();

		// Get the parent
		$parent_id = $this->form->getValue('parent_id', 0);

		if (empty($parent_id))
		{
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id', 'value'))
			->select($db->quoteName('title', 'text'))
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('published') . ' >= 0')
			->where($db->quoteName('parent_id') . ' = ' . (int) $parent_id);

		if ($menuType = $this->form->getValue('menutype'))
		{
			$query->where($db->quoteName('menutype') . ' = ' . $db->quote($menuType));
		}
		else
		{
			$query->where($db->quoteName('menutype') . ' != ' . $db->quote(''));
		}

		$query->order($db->quoteName('lft') . ' ASC ');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$options = array_merge(
			array(array('value' => '-1', 'text' => JText::_('PLG_CONTENT_CREATEMENULINK_MENUS_ITEM_FIELD_ORDERING_VALUE_FIRST'))),
			$options,
			array(array('value' => '-2', 'text' => JText::_('PLG_CONTENT_CREATEMENULINK_MENUS_ITEM_FIELD_ORDERING_VALUE_LAST')))
		);

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		if ($this->form->getValue('id', 0) == 0)
		{
			return '<span class="readonly">' . JText::_('PLG_CONTENT_CREATEMENULINK_MENUS_ITEM_FIELD_ORDERING_TEXT') . '</span>';
		}

		return parent::getInput();
	}
}
