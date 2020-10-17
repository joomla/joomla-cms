<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;

/**
 * Editor Contact button
 *
 * @since  3.7.0
 */
class PlgButtonContact extends CMSPlugin
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
	 * @return  CMSObject  The button options as JObject
	 *
	 * @since   3.7.0
	 */
	public function onDisplay($name)
	{
		$user  = Factory::getUser();

		if ($user->authorise('core.create', 'com_contact')
			|| $user->authorise('core.edit', 'com_contact')
			|| $user->authorise('core.edit.own', 'com_contact'))
		{
			// The URL for the contacts list
			$link = 'index.php?option=com_contact&amp;view=contacts&amp;layout=modal&amp;tmpl=component&amp;'
				. Session::getFormToken() . '=1&amp;editor=' . $name;

		$button = new CMSObject;
		$button->modal   = true;
		$button->link    = $link;
		$button->text    = Text::_('PLG_EDITORS-XTD_CONTACT_BUTTON_CONTACT');
		$button->name    = 'address';
		$button->iconSVG = '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M2 14h3v5h-3zM2 8h3v5h-3zM2 20h3v5h-3zM2 26h3v5h-3zM6 0v32h'
							. '24v-32h-24zM18 8.010c2.203 0 3.99 1.786 3.99 3.99s-1.786 3.99-3.99 3.99c-2.203 0-3.99-1.786-3.99-3.99s1.786-3.99 3'
							. '.99-3.99zM24 24h-12v-2c0-2.209 1.791-4 4-4v0h4c2.209 0 4 1.791 4 4v2zM2 2h3v5h-3z"></path></svg>';
		$button->options = [
			'height' => '300px',
			'width'  => '800px',
			'bodyHeight'  => '70',
			'modalWidth'  => '80',
		];

			return $button;
		}
	}
}
