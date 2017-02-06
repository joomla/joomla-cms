<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.module
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Module button
 *
 * @since  3.5
 */
class PlgButtonModule extends JPlugin
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
	 * @return  JObject  The button options as JObject
	 *
	 * @since   3.5
	 */
	public function onDisplay($name)
	{
		/*
		 * Use the built-in element view to select the module.
		 * Currently uses blank class.
		 */
		if (JPluginHelper::isEnabled('content', 'loadmodule'))
		{
			$user  = JFactory::getUser();

			if ($user->authorise('core.create', 'com_modules')
				|| $user->authorise('core.edit', 'com_modules')
				|| $user->authorise('core.edit.own', 'com_modules'))
			{
				$link = 'index.php?option=com_modules&amp;view=modules&amp;layout=modal&amp;tmpl=component&amp;editor='
						. $name . '&amp;' . JSession::getFormToken() . '=1';

				$button          = new JObject;
				$button->modal   = true;
				$button->class   = 'btn';
				$button->link    = $link;
				$button->text    = JText::_('PLG_MODULE_BUTTON_MODULE');
				$button->name    = 'file-add';
				$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

				return $button;
			}
		}
	}
}
