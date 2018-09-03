<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Field\Modal;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Fields Modal Field
 *
 * @since  4.0.0
 */
class FieldField extends FormField
{
	protected $type = 'Modal_Field';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   4.0.0
	 */
	protected function getInput ()
	{
		if ($this->element['context'])
		{
			$context = (string) $this->element['context'];
		}
		else
		{
			$context = (string) Factory::getApplication()->input->get('context', 'com_content');
		}

		$allowEdit  = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language
		Factory::getLanguage()->load('com_fields', JPATH_ADMINISTRATOR);

		$jsId = $this->id;

		// Build the script.
		// Select button script
		$script = <<<JS1
function jSelectCategory_$jsId(id, title, object) {
	document.getElementById("$jsId" + "_id").value = id;
	document.getElementById("$jsId" + "_name").value = title;
JS1;

		if ($allowEdit)
		{
			$script += <<<JS2
	document.getElementById("$jsId" + "_edit").classList.remove("hidden");
JS2;
		}

		if ($allowClear)
		{
			$script += <<<JS3
	document.getElementById("$jsId" + "_clear").classList.remove("hidden");
JS3;
		}

		$script += <<<JS4
	Joomla.Modal.getCurrent().close();
}
JS4;

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;
			$jsValue = htmlspecialchars(Text::_('COM_FIELDS_SELECT_A_FIELD', true), ENT_COMPAT, 'UTF-8');
			$script += <<<JS5
function jClearCategory(id) {
	document.getElementById(id + "_id").value = "";
	document.getElementById(id + "_name").value = "$jsValue";
	document.getElementById(id + "_clear").classList.add("hidden");
	if (document.getElementById(id + "_edit")) {
		document.getElementById(id + "_edit").classList.add("hidden");
	}
	return false;
}
JS5;
		}

		// @todo move the script to a file
		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration($script);

		// Setup variables for display.
		$html = array();
		$link = 'index.php?option=com_fields&amp;view=fields&amp;layout=modal&amp;tmpl=component&amp;context=' . $context .
			'&amp;function=jSelectCategory_' . $this->id;

		if (isset($this->element['language']))
		{
			$link .= '&amp;forcedLanguage=' . $this->element['language'];
		}

		if ((int) $this->value > 0)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__fields'))
				->where($db->quoteName('id') . ' = ' . (int) $this->value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		if (empty($title))
		{
			$title = Text::_('COM_FIELDS_SELECT_A_FIELD');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The active field id field.
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// The current field display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35">';
		$html[] = '<a href="#modalCategory-' . $this->id . '" class="btn hasTooltip" role="button"  data-toggle="modal"' . ' title="' .
			HTMLHelper::tooltipText('COM_FIELDS_CHANGE_FIELD') . '">' . '<span class="icon-file"></span> ' . Text::_('JSELECT') . '</a>';

		// Edit field button
		if ($allowEdit)
		{
			$html[] = '<a' . ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"' .
					' href="index.php?option=com_fields&layout=modal&tmpl=component&task=field.edit&id=' . $value . '"' . ' target="_blank"' .
					' title="' . HTMLHelper::tooltipText('COM_FIELDS_EDIT_FIELD') . '" >' . '<span class="icon-edit"></span>' . Text::_('JACTION_EDIT') .
					'</a>';

			$html[] = HTMLHelper::_(
				'bootstrap.renderModal', 'modalCategory-' . $this->id,
				array(
					'url' => $link . '&amp;' . Session::getFormToken() . '=1"',
					'title' => Text::_('COM_FIELDS_SELECT_A_FIELD'),
					'width' => '800px',
					'height' => '300px',
					'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">' . Text::_("JLIB_HTML_BEHAVIOR_CLOSE") .
						'</button>'
				)
			);
		}

		// Clear field button
		if ($allowClear)
		{
			$html[] = '<button' . ' id="' . $this->id . '_clear"' . ' class="btn' . ($value ? '' : ' hidden') . '"' .
					' onclick="return jClearCategory(\'' . $this->id . '\')">' . '<span class="icon-remove"></span>' . Text::_('JCLEAR') .
					'</button>';
		}

		$html[] = '</span>';

		// Note: class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '">';

		return implode("\n", $html);
	}
}
