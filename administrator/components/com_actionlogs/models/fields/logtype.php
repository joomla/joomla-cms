<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  System.actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;

JFormHelper::loadFieldClass('checkboxes');
JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

/**
 * Field to load a list of all users that have logged actions
 *
 * @since 3.9.0
 */
class JFormFieldLogType extends JFormFieldCheckboxes
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $type = 'LogType';

	/**
	 * Method to get the field input markup for check boxes.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getInput()
	{
		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		$script = "
			// Checks/Uncheck all checkboxes
			function eventsCheckAll(value)
			{
				events = document.querySelectorAll('#" . $this->id . ".checkboxes input');

				for (i = 0; i < events.length; i++) {
					events[i].checked = value;
				}
			}
		";

		// Add the script to the document head
		JFactory::getDocument()->addScriptDeclaration($script);

		$html = '<div class="well well-small">';
		$html .= '<div class="form-inline">';
		$html .= '<span class="small">' . JText::_('JSELECT') . ': ';
		$html .= '<a id="checkAll" href="javascript://" onclick="eventsCheckAll(true)">' . JText::_('JALL') . '</a>, ';
		$html .= '<a id="uncheckAll" href="javascript://" onclick="eventsCheckAll(false)">' . JText::_('JNONE') . '</a>';
		$html .= '</span>';
		$html .= '</div>';
		$html .= '<hr class="hr-condensed" />';

		/**
		 * The format of the input tag to be filled in using sprintf.
		 *     %1 - id
		 *     %2 - name
		 *     %3 - value
		 *     %4 = any other attributes
		 */
		$format = '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s" %4$s />';

		// Initialize the field checked options.
		$checkedOptions = is_array($this->value) ? $this->value : explode(',', (string) $this->value);

		// Get the field options.
		$options = $this->getOptions();

		$html .= '<fieldset id="' . $this->id . '" class="' . trim($this->class . ' checkboxes') . '">';

		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			$checked = in_array((string) $option->value, $checkedOptions, true) ? 'checked' : '';
			$oid     = $this->id . $i;
			$value   = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');

			$html .= '<label for="' . $oid . '" class="checkbox">';
			$html .= sprintf($format, $oid, $this->name, $value, $checked);
			$html .= $option->text . '</label>';
		}

		$html .= '</fieldset>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.9.0
	 */
	public function getOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension'))
			->from($db->quoteName('#__action_logs_extensions'));

		$extensions = $db->setQuery($query)->loadColumn();

		$options = array();

		foreach ($extensions as $extension)
		{
			ActionlogsHelper::loadTranslationFiles($extension);
			$option = JHtml::_('select.option', $extension, JText::_($extension));
			$options[ApplicationHelper::stringURLSafe(JText::_($extension)) . '_' . $extension] = (object) $option;
		}

		ksort($options);

		return array_merge(parent::getOptions(), array_values($options));
	}
}
