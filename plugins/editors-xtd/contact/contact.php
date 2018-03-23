<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Object\CMSObject;

/**
 * Editor Contact buton
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
