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

		// Build the script.
		$script = array();

		// Select button script
		$script[] = '	function jSelectCategory_' . $this->id . '(id, title, object) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';

		if ($allowEdit)
		{
			$script[] = '		document.getElementById("' . $this->id . '_edit").classList.remove("hidden");';
		}

		if ($allowClear)
		{
			$script[] = '		document.getElementById("' . $this->id . '_clear").classList.remove("hidden");';
		}

		$script[] = '		Joomla.Modal.getCurrent().close()';
		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearCategory(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "' .
				htmlspecialchars(\JText::_('COM_FIELDS_SELECT_A_FIELD', true), ENT_COMPAT, 'UTF-8') . '";';
			$script[] = '		document.getElementById(id + "_clear").classList.add("hidden");';
			$script[] = '		if (document.getElementById(id + "_edit")) {';
			$script[] = '			document.getElementById(id + "_edit").classList.add("hidden");';
			$script[] = '		}';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

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
				\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		if (empty($title))
		{
			$title = \JText::_('COM_FIELDS_SELECT_A_FIELD');
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
			\JHtml::tooltipText('COM_FIELDS_CHANGE_FIELD') . '">' . '<span class="icon-file"></span> ' . \JText::_('JSELECT') . '</a>';

		// Edit field button
		if ($allowEdit)
		{
			$html[] = '<a' . ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"' .
					' href="index.php?option=com_fields&layout=modal&tmpl=component&task=field.edit&id=' . $value . '"' . ' target="_blank"' .
					' title="' . \JHtml::tooltipText('COM_FIELDS_EDIT_FIELD') . '" >' . '<span class="icon-edit"></span>' . \JText::_('JACTION_EDIT') .
					'</a>';

			$html[] = \JHtml::_(
				'bootstrap.renderModal', 'modalCategory-' . $this->id,
				array(
					'url' => $link . '&amp;' . \JSession::getFormToken() . '=1"',
					'title' => \JText::_('COM_FIELDS_SELECT_A_FIELD'),
					'width' => '800px',
					'height' => '300px',
					'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">' . \JText::_("JLIB_HTML_BEHAVIOR_CLOSE") .
						'</button>'
				)
			);
		}

		// Clear field button
		if ($allowClear)
		{
			$html[] = '<button' . ' id="' . $this->id . '_clear"' . ' class="btn' . ($value ? '' : ' hidden') . '"' .
					' onclick="return jClearCategory(\'' . $this->id . '\')">' . '<span class="icon-remove"></span>' . \JText::_('JCLEAR') .
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
