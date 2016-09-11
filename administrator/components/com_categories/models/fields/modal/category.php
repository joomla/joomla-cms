<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal article picker.
 *
 * @since  3.1
 */
class JFormFieldModal_Category extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   1.6
	 */
	protected $type = 'Modal_Category';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if ($this->element['extension'])
		{
			$extension = (string) $this->element['extension'];
		}
		else
		{
			$extension = (string) JFactory::getApplication()->input->get('extension', 'com_content');
		}

		$allowEdit  = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language
		JFactory::getLanguage()->load('com_categories', JPATH_ADMINISTRATOR);

		// The active category id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Build the script.
		$script = array();

		// Select button script
		$script[] = '	function jSelectCategory_' . $this->id . '(id, title, object) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';

		if ($allowEdit)
		{
			$script[] = '		if (id == "' . (int) $this->value . '") {';
			$script[] = '			jQuery("#' . $this->id . '_edit").removeClass("hidden");';
			$script[] = '		} else {';
			$script[] = '			jQuery("#' . $this->id . '_edit").addClass("hidden");';
			$script[] = '		}';
		}

		if ($allowClear)
		{
			$script[] = '		jQuery("#' . $this->id . '_clear").removeClass("hidden");';
		}

		$script[] = '		jQuery("#categorySelect' . $this->id . 'Modal").modal("hide");';
		$script[] = '	}';

		// Edit button script
		$script[] = '	function jEditCategory_' . $value . '(title) {';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';
		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearCategory(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "'
				. htmlspecialchars(JText::_('COM_CATEGORIES_SELECT_A_CATEGORY', true), ENT_COMPAT, 'UTF-8') . '";';
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

		$linkCategories = 'index.php?option=com_categories&amp;view=categories&amp;layout=modal&amp;tmpl=component'
			. '&amp;extension=' . $extension
			. '&amp;function=jSelectCategory_' . $this->id;

		$linkCategory   = 'index.php?option=com_categories&amp;view=category&amp;layout=modal&amp;tmpl=component'
			. '&amp;task=category.edit'
			. '&amp;function=jEditCategory_' . $value;

		if (isset($this->element['language']))
		{
			$linkCategories .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkCategory   .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle = JText::_('COM_CATEGORIES_CHANGE_CATEGORY') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle = JText::_('COM_CATEGORIES_CHANGE_CATEGORY');
		}

		$urlSelect = $linkCategories . '&amp;' . JSession::getFormToken() . '=1';
		$urlEdit   = $linkCategory . '&amp;id=' . $value . '&amp;' . JSession::getFormToken() . '=1';

		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__categories'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
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
			$title = JText::_('COM_CATEGORIES_SELECT_A_CATEGORY', true);
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current category display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select category button
		$html[] = '<a'
			. ' class="btn hasTooltip"'
			. ' data-toggle="modal"'
			. ' role="button"'
			. ' href="#categorySelect' . $this->id . 'Modal"'
			. ' title="' . JHtml::tooltipText('COM_CATEGORIES_CHANGE_CATEGORY') . '">'
			. '<span class="icon-file"></span> ' . JText::_('JSELECT')
			. '</a>';

		// Edit category button
		if ($allowEdit)
		{
			$html[] = '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#categoryEdit' . $value . 'Modal"'
				. ' title="' . JHtml::tooltipText('COM_CATEGORIES_EDIT_CATEGORY') . '">'
				. '<span class="icon-edit"></span> ' . JText::_('JACTION_EDIT')
				. '</a>';
		}

		// Clear category button
		if ($allowClear)
		{
			$html[] = '<button'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' onclick="return jClearCategory(\'' . $this->id . '\')">'
				. '<span class="icon-remove"></span>' . JText::_('JCLEAR')
				. '</button>';
		}

		$html[] = '</span>';

		// Select category modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'categorySelect' . $this->id . 'Modal',
			array(
				'title'       => $modalTitle,
				'url'         => $urlSelect,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<a type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
						. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>',
			)
		);

		// Edit category modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'categoryEdit' . $value . 'Modal',
			array(
				'title'       => JText::_('COM_CATEGORIES_EDIT_CATEGORY'),
				'backdrop'    => 'static',
				'keyboard'    => false,
				'closeButton' => false,
				'url'         => $urlEdit,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<a type="button" class="btn" data-dismiss="modal" aria-hidden="true"'
						. ' onclick="jQuery(\'#categoryEdit' . $value . 'Modal iframe\').contents().find(\'#closeBtn\').click();">'
						. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>'
						. '<button type="button" class="btn btn-primary" aria-hidden="true"'
						. ' onclick="jQuery(\'#categoryEdit' . $value . 'Modal iframe\').contents().find(\'#saveBtn\').click();">'
						. JText::_("JSAVE") . '</button>'
						. '<button type="button" class="btn btn-success" aria-hidden="true"'
						. ' onclick="jQuery(\'#categoryEdit' . $value . 'Modal iframe\').contents().find(\'#applyBtn\').click();">'
						. JText::_("JAPPLY") . '</button>',
			)
		);

		// Note: class='required' for client side validation
		$class = $this->required ? ' class="required modal-value"' : '';

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
