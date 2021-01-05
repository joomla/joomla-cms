<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.menu
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor menu button
 *
 * @since  3.7.0
 */
class PlgButtonMenu extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @since  3.7.0
	 * @return array
	 */
	public function onDisplay($name)
	{
		/*
		 * Use the built-in element view to select the menu item.
		 * Currently uses blank class.
		 */
		$user  = JFactory::getUser();

		if ($user->authorise('core.create', 'com_menus')
			|| $user->authorise('core.edit', 'com_menus'))
		{
		$link = 'index.php?option=com_menus&amp;view=items&amp;layout=modal&amp;tmpl=component&amp;'
			. JSession::getFormToken() . '=1&amp;editor=' . $name;

		$button          = new JObject;
		$button->modal   = true;
		$button->class   = 'btn';
		$button->link    = $link;
		$button->text    = JText::_('PLG_EDITORS-XTD_MENU_BUTTON_MENU');
		$button->name    = 'share-alt';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

		return $button;
		}
	}
}
