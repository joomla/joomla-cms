<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.image
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Image buton
 *
 * @since  1.5
 */
class PlgButtonImage extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button.
	 *
	 * @param   string   $name    The name of the button to display.
	 * @param   string   $asset   The name of the asset being edited.
	 * @param   integer  $author  The id of the author owning the asset being edited.
	 *
	 * @return  JObject  The button options as JObject or false if not allowed
	 *
	 * @since   1.5
	 */
	public function onDisplay($name, $asset, $author)
	{
		$app       = JFactory::getApplication();
		$user      = JFactory::getUser();
		$extension = $app->input->get('option');

		// For categories we check the extension (ex: component.section)
		if ($extension === 'com_categories')
		{
			$parts     = explode('.', $app->input->get('extension', 'com_content'));
			$extension = $parts[0];
		}

		$asset = $asset !== '' ? $asset : $extension;

		if ($user->authorise('core.edit', $asset)
			|| $user->authorise('core.create', $asset)
			|| (count($user->getAuthorisedCategories($asset, 'core.create')) > 0)
			|| ($user->authorise('core.edit.own', $asset) && $author === $user->id)
			|| (count($user->getAuthorisedCategories($extension, 'core.edit')) > 0)
			|| (count($user->getAuthorisedCategories($extension, 'core.edit.own')) > 0 && $author === $user->id))
		{
			$link = 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;e_name=' . $name . '&amp;asset=' . $asset . '&amp;author=' . $author;

			$button = new JObject;
			$button->modal   = true;
			$button->class   = 'btn';
			$button->link    = $link;
			$button->text    = JText::_('PLG_IMAGE_BUTTON_IMAGE');
			$button->name    = 'pictures';
			$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

			return $button;
		}

		return false;
	}
}
