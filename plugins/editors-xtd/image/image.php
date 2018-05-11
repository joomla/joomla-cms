<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Object\CMSObject;

/**
 * Editor Image buton
 *
 * @since  1.5
 */
class PlgButtonImage extends CMSPlugin
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
	 * @return  CMSObject|false
	 *
	 * @since   1.5
	 */
	public function onDisplay($name, $asset, $author)
	{
		$app       = Factory::getApplication();
		$user      = Factory::getUser();
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
			$link = 'index.php?option=com_media&amp;tmpl=component&amp;e_name=' . $name . '&amp;asset=' . $asset . '&amp;author=' . $author;

			$button = new CMSObject;
			$button->modal   = true;
			$button->link    = $link;
			$button->text    = Text::_('PLG_IMAGE_BUTTON_IMAGE');
			$button->name    = 'pictures';
			$button->options = [
				'height'     => '400px',
				'width'      => '800px',
				'bodyHeight' => '70',
				'modalWidth' => '80',
				'tinyPath'   => $link,
				'confirmCallback' => 'Joomla.getImage(Joomla.selectedFile, \'' . $name . '\')',
				'confirmText' => 'insert image' // Needs to be translated
			];

			return $button;
		}

		return false;
	}
}
