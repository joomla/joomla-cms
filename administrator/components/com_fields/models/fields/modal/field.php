<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Fields Modal Field
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldModal_Field extends JFormField
{
	protected $type = 'Modal_Field';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput ()
	{
		if ($this->element['context'])
		{
			$context = (string) $this->element['context'];
		}
		else
		{
			$context = (string) JFactory::getApplication()->input->get('context', 'com_content');
		}

		$allowEdit  = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language
		JFactory::getLanguage()->load('com_fields', JPATH_ADMINISTRATOR);

		// Build the script.
		$script = array();

		// Select button script
		$script[] = '	function jSelectCategory_' . $this->id . '(id, title, object) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';

		if ($allowEdit)
		{
			$script[] = '		jQuery("#' . $this->id . '_edit").removeClass("hidden");';
		}

		if ($allowClear)
		{
			$script[] = '		jQuery("#' . $this->id . '_clear").removeClass("hidden");';
		}

		$script[] = '		jQuery("#modalCategory-' . $this->id . '").modal("hide");';
		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearCategory(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "' .
				htmlspecialchars(JText::_('COM_FIELDS_SELECT_A_FIELD', true), ENT_COMPAT, 'UTF-8') . '";';
			$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '		if (document.getElementById(id + "_edit")) {';
			$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
			$script[] = '		}';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

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
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__fields'))
				->where($db->quoteName('id') . ' = ' . (int) $this->value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}

		if (empty($title))
		{
			$title = JText::_('COM_FIELDS_SELECT_A_FIELD');
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
			JHtml::tooltipText('COM_FIELDS_CHANGE_FIELD') . '">' . '<span class="icon-file"></span> ' . JText::_('JSELECT') . '</a>';

		// Edit field button
		if ($allowEdit)
		{
			$html[] = '<a' . ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"' .
					' href="index.php?option=com_fields&layout=modal&tmpl=component&task=field.edit&id=' . $value . '"' . ' target="_blank"' .
					' title="' . JHtml::tooltipText('COM_FIELDS_EDIT_FIELD') . '" >' . '<span class="icon-edit"></span>' . JText::_('JACTION_EDIT') .
					'</a>';

			$html[] = JHtml::_(
				'bootstrap.renderModal', 'modalCategory-' . $this->id,
				array(
					'url' => $link . '&amp;' . JSession::getFormToken() . '=1"',
					'title' => JText::_('COM_FIELDS_SELECT_A_FIELD'),
					'width' => '800px',
					'height' => '300px',
					'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_("JLIB_HTML_BEHAVIOR_CLOSE") .
						'</button>'
				)
			);
		}

		// Clear field button
		if ($allowClear)
		{
			$html[] = '<button' . ' id="' . $this->id . '_clear"' . ' class="btn' . ($value ? '' : ' hidden') . '"' .
					' onclick="return jClearCategory(\'' . $this->id . '\')">' . '<span class="icon-remove"></span>' . JText::_('JCLEAR') .
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
