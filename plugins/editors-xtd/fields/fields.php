<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.fields
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Fields button
 *
 * @since  3.7.0
 */
class PlgButtonFields extends JPlugin
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
	 * @since  3.7.0
	 */
	public function onDisplay($name)
	{
		// Check if com_fields is enabled
		if (!JComponentHelper::isEnabled('com_fields'))
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
