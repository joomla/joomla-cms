<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.module
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Module buton
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
	 * @since  3.5
	 * @return array
	 */
	public function onDisplay()
	{
		// Button should not be available when editing modules
		if (JFactory::getApplication()->input->get('option') == 'com_modules')
		{
			return array();
		}

		/*
		 * Use the built-in element view to select the article.
		 * Currently uses blank class.
		 */
		$link = 'index.php?option=com_modules&amp;view=modules&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';

		$button = new JObject;
		$button->modal = true;
		$button->class = 'btn';
		$button->link = $link;
		$button->text = JText::_('PLG_MODULE_BUTTON_MODULE');
		$button->name = 'file-add';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

		return $button;
	}
}
