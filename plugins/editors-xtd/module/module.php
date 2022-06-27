<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.module
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;

/**
 * Editor Module button
 *
 * @since  3.5
 */
class PlgButtonModule extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  CMSObject|void  The button options as CMSObject
	 *
	 * @since   3.5
	 */
	public function onDisplay($name)
	{
		/*
		 * Use the built-in element view to select the module.
		 * Currently uses blank class.
		 */
		$user  = Factory::getUser();

		if ($user->authorise('core.create', 'com_modules')
			|| $user->authorise('core.edit', 'com_modules')
			|| $user->authorise('core.edit.own', 'com_modules'))
		{
			$link = 'index.php?option=com_modules&amp;view=modules&amp;layout=modal&amp;tmpl=component&amp;editor='
					. $name . '&amp;' . Session::getFormToken() . '=1';
			$button = new CMSObject;
			$button->modal   = true;
			$button->link    = $link;
			$button->text    = Text::_('PLG_MODULE_BUTTON_MODULE');
			$button->name    = $this->_type . '_' . $this->_name;
			$button->icon    = 'cube';
			$button->iconSVG = '<svg viewBox="0 0 512 512" width="24" height="24"><path d="M239.1 6.3l-208 78c-18.7 7-31.1 '
				. '25-31.1 45v225.1c0 18.2 10.3 34.8 26.5 42.9l208 104c13.5 6.8 29.4 6.8 42.9 0l208-104c16.3-8.1 26.5-24.8 '
				. '26.5-42.9V129.3c0-20-12.4-37.9-31.1-44.9l-208-78C262 2.2 250 2.2 239.1 6.3zM256 68.4l192 72v1.1l-192 '
				. '78-192-78v-1.1l192-72zm32 356V275.5l160-65v133.9l-160 80z"></path></svg>';
			$button->options = [
				'height'     => '300px',
				'width'      => '800px',
				'bodyHeight' => '70',
				'modalWidth' => '80',
			];

			return $button;
		}
	}
}
