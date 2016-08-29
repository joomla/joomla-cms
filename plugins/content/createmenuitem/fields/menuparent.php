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
class JFormFieldMenuParent extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since   __DEPLOY_VERSION__
	 */
	protected $type = 'MenuParent';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$options = array();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('a.id', 'value'))
			->select($db->quoteName('a.title', 'text'))
			->select($db->quoteName('a.level'))
			->from($db->quoteName('#__menu', 'a'))
			->join('LEFT', $db->qn('#__menu', 'b') . 'ON' . $db->qn('a.lft') . ' > ' . $db->qn('b.lft') . 'AND' . $db->qn('a.rgt') . ' < ' . $db->qn('b.rgt'));

		if ($menuType = $this->form->getValue('menutype'))
		{
			$query->where($db->quoteName('a.menutype') . ' = ' . $db->quote($menuType));
		}
		else
		{
			$query->where($db->quoteName('a.menutype') . ' != ' . $db->quote(''));
		}

		// Prevent parenting to children of this item.
		if ($id = $this->form->getValue('id'))
		{
			$query->join('LEFT', $db->quoteName('#__menu', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn((int) $id))
				->where('NOT(' . $db->quoteName('a.lft') . ' >= ' . $db->qn('p.lft') . ' AND ' . $db->qn('a.rgt') . ' <= ' . $db->qn('p.rgt') . ' ) ');
		}

		$query->where($db->quoteName('a.published') . ' != -2 ')
			->group($db->quoteName(array('a.id', 'a.title', 'a.level', 'a.lft', 'a.rgt', 'a.mentype', 'a.parent_id', 'a.published')))
			->order($db->quoteName('a.lft') . ' ASC ');

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

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
