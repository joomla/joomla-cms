<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

JFormHelper::loadFieldClass('GroupedList');

/**
 * Supports an HTML select list of menus
 *
 * @since  1.6
 */
class JFormFieldMenu extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Menu';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	protected function getGroups()
	{
		$clientId   = (string) $this->element['clientid'];
		$accessType = (string) $this->element['accesstype'];
		$showAll    = (string) $this->element['showAll'] == 'true';

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn(array('id', 'menutype', 'title', 'client_id'), array('id', 'value', 'text', 'client_id')))
			->from($db->quoteName('#__menu_types'))
			->order('client_id, title');

		if (strlen($clientId))
		{
			$query->where('client_id = ' . (int) $clientId);
		}

		$menus = $db->setQuery($query)->loadObjectList();

		if ($accessType)
		{
			$user = JFactory::getUser();

			foreach ($menus as $key => $menu)
			{
				switch ($accessType)
				{
					case 'create':
					case 'manage':
						if (!$user->authorise('core.' . $accessType, 'com_menus.menu.' . (int) $menu->id))
						{
							unset($menus[$key]);
						}
						break;

					// Editing a menu item is a bit tricky, we have to check the current menutype for core.edit and all others for core.create
					case 'edit':
						$check = $this->value == $menu->value ? 'edit' : 'create';

						if (!$user->authorise('core.' . $check, 'com_menus.menu.' . (int) $menu->id))
						{
							unset($menus[$key]);
						}
						break;
				}
			}
		}

		$opts = array();

		// Protected menutypes can be shown if requested
		if ($clientId == 1 && $showAll)
		{
			$opts[] = (object) array(
				'value'     => 'main',
				'text'      => JText::_('COM_MENUS_MENU_TYPE_PROTECTED_MAIN_LABEL'),
				'client_id' => 1,
			);
		}

		$options = array_merge($opts, $menus);
		$groups  = array();

		if (strlen($clientId))
		{
			$groups[0] = $options;
		}
		else
		{
			foreach ($options as $option)
			{
				// If client id is not specified we group the items.
				$label = ($option->client_id == 1 ? JText::_('JADMINISTRATOR') : JText::_('JSITE'));

				$groups[$label][] = $option;
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getGroups(), $groups);
	}
}
