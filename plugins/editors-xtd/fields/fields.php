<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.fields
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Editor Fields button
 *
 * @since  3.7.0
 */
class PlgButtonFields extends CMSPlugin
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
	 * @since  3.7.0
	 */
	public function onDisplay($name)
	{
		// Check if com_fields is enabled
		if (!ComponentHelper::isEnabled('com_fields'))
		{
			return;
		}

		// Register FieldsHelper
		JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

		// Guess the field context based on view.
		$jinput = Factory::getApplication()->input;
		$context = $jinput->get('option') . '.' . $jinput->get('view');

		// Validate context.
		$context = implode('.', FieldsHelper::extract($context));
		if (!FieldsHelper::getFields($context))
		{
			return;
		}

		$link = 'index.php?option=com_fields&amp;view=fields&amp;layout=modal&amp;tmpl=component&amp;context='
			. $context . '&amp;editor=' . $name . '&amp;' . Session::getFormToken() . '=1';

		$button          = new CMSObject;
		$button->modal   = true;
		$button->link    = $link;
		$button->text    = Text::_('PLG_EDITORS-XTD_FIELDS_BUTTON_FIELD');
		$button->name    = 'puzzle';
		$button->options = [
			'height'     => '300px',
			'width'      => '800px',
			'bodyHeight' => '70',
			'modalWidth' => '80',
		];

		return $button;
	}
}
