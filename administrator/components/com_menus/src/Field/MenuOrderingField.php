<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

/**
 * Menu Ordering field.
 *
 * @since  1.6
 */
class MenuOrderingField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since   1.7
	 */
	protected $type = 'MenuOrdering';

	/**
	 * Method to get the list of siblings in a menu.
	 * The method requires that parent be set.
	 *
	 * @return  array  The field option objects or false if the parent field has not been set
	 *
	 * @since   1.7
	 */
	protected function getOptions()
	{
		$options = array();

		// Get the parent
		$parent_id = (int) $this->form->getValue('parent_id', 0);

		if (!$parent_id)
		{
			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('a.id', 'value'),
					$db->quoteName('a.title', 'text'),
					$db->quoteName('a.client_id', 'clientId'),
				]
			)
			->from($db->quoteName('#__menu', 'a'))

			->where($db->quoteName('a.published') . ' >= 0')
			->where($db->quoteName('a.parent_id') . ' = :parentId')
			->bind(':parentId', $parent_id, ParameterType::INTEGER);

		if ($menuType = $this->form->getValue('menutype'))
		{
			$query->where($db->quoteName('a.menutype') . ' = :menuType')
				->bind(':menuType', $menuType);
		}
		else
		{
			$query->where($db->quoteName('a.menutype') . ' != ' . $db->quote(''));
		}

		$query->order($db->quoteName('a.lft') . ' ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		// Allow translation of custom admin menus
		foreach ($options as &$option)
		{
			if ($option->clientId != 0)
			{
				$option->text = Text::_($option->text);
			}
		}

		$options = array_merge(
			array(array('value' => '-1', 'text' => Text::_('COM_MENUS_ITEM_FIELD_ORDERING_VALUE_FIRST'))),
			$options,
			array(array('value' => '-2', 'text' => Text::_('COM_MENUS_ITEM_FIELD_ORDERING_VALUE_LAST')))
		);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7
	 */
	protected function getInput()
	{
		if ($this->form->getValue('id', 0) == 0)
		{
			return '<span class="readonly">' . Text::_('COM_MENUS_ITEM_FIELD_ORDERING_TEXT') . '</span>';
		}
		else
		{
			return parent::getInput();
		}
	}
}
