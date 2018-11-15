<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Contact buton
 *
 * @since  3.7.0
 */
class PlgButtonContact extends JPlugin
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
	 * @return  JObject  The button options as JObject
	 *
	 * @since   3.7.0
	 */
	public function onDisplay($name)
	{
		$user  = JFactory::getUser();
		$client = JFactory::getApplication()->client;

		if ($user->authorise('core.create', 'com_contact')
			|| $user->authorise('core.edit', 'com_contact')
			|| $user->authorise('core.edit.own', 'com_contact'))
		{
			// The URL for the contacts list
			$link = 'index.php?option=com_contact&amp;view=contacts&amp;layout=modal&amp;tmpl=component&amp;'
				. JSession::getFormToken() . '=1&amp;editor=' . $name;

			$button          = new JObject;
			$button->modal   = true;
			$button->class   = 'btn';
			$button->link    = $link;
			$button->text    = JText::_('PLG_EDITORS-XTD_CONTACT_BUTTON_CONTACT');
			$button->name    = 'address';

			// We check if the webclient is a phone
			if (($client->platform == $client::ANDROID)
				|| ($client->platform == $client::WINDOWS_PHONE)
				|| ($client->platform == $client::IPHONE)
				|| ($client->platform == $client::BLACKBERRY))
			{
				$button->options = "{handler: 'iframe', size: {x: 300, y: 500}}";
			}
			else
			{
				$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";
			}

			return $button;
		}
	}
}
