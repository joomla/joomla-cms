<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

/**
 * Supports an HTML select list of menus
 *
 * @since  1.6
 */
class JFormFieldMenu extends JFormAbstractlist
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Menu';

	/**
	 * Method to get the list of menus for the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$clientId   = (string) $this->element['clientid'];
		$accessType = (string) $this->element['accesstype'];
		$showAll    = (string) $this->element['showAll'] == 'true';

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn(array('id', 'menutype', 'title'), array('id', 'value', 'text')))
			->from($db->quoteName('#__menu_types'))
			->order('title');

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

		// Merge any additional options in the XML definition.
		$opts = parent::getOptions();

		// Protected menutypes can be shown if requested
		if ($clientId == 1 && $showAll)
		{
			$opts[] = (object) array(
				'value' => 'main',
				'text'  => JText::_('COM_MENUS_MENU_TYPE_PROTECTED_MAIN_LABEL'),
			);

			$opts[] = (object) array(
				'value' => 'menu',
				'text'  => JText::_('COM_MENUS_MENU_TYPE_PROTECTED_MENU_LABEL'),
			);
		}

		$options = array_merge($opts, $menus);

		return $options;
	}
}
