<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Fields button
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgButtonFields extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  JObject  The button options as JObject
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onDisplay($name)
	{
		// Do not display button if content and system plugins are disabled
		if (!JPluginHelper::isEnabled('content', 'fields') && !JPluginHelper::isEnabled('system', 'fields'))
		{
			return;
		}

		// Register FieldsHelper
		JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

		// Guess the field context based on view.
		$jinput = JFactory::getApplication()->input;
		$context = $jinput->get('option') . '.' . $jinput->get('view');

		// Validate context.
		$context = implode('.', FieldsHelper::extract($context));
		if (!FieldsHelper::getFields($context))
		{
			return;
		}

		$link = 'index.php?option=com_fields&amp;view=fields&amp;layout=modal&amp;tmpl=component&amp;context='
			. $context . '&amp;editor=' . $name . '&amp;' . JSession::getFormToken() . '=1';

		$button          = new JObject;
		$button->modal   = true;
		$button->class   = 'btn';
		$button->link    = $link;
		$button->text    = JText::_('PLG_EDITORS-XTD_FIELDS_BUTTON_FIELD');
		$button->name    = 'puzzle';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

		return $button;
	}
}
