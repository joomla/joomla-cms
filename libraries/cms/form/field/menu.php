<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of menus
 *
 * @since  1.6
 */
class JFormFieldMenu extends JFormFieldList
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
		$menus = JHtml::_('menu.menus');

		$accesstype = $this->element['accesstype'];

		if ($accesstype)
		{
			$user = JFactory::getUser();

			foreach ($menus as $key => $menu)
			{
				switch ($accesstype)
				{
					case 'create':
					case 'manage':
						if (!$user->authorise('core.' . $accesstype, 'com_menus.menu.' . (int) $menu->id))
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
		$options = array_merge(parent::getOptions(), $menus);

		return $options;
	}
}
