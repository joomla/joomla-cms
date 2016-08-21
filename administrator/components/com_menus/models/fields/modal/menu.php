<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
JHtml::_('bootstrap.tooltip', '.hasTooltip');

/**
 * Supports a modal menu item picker.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldModal_Menu extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   __DEPLOY_VERSION__
	 */
	protected $type = 'Modal_Menu';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		$allowEdit  = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language
		JFactory::getLanguage()->load('com_menus', JPATH_ADMINISTRATOR);

		// The active article id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Build the script.
		$script = array();

		// Select button script
		$script[] = '	function jSelectMenu_' . $this->id . '(id, title, object) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';

		if ($allowEdit)
		{
			$script[] = '		if (id == "' . $value . '") {';
			$script[] = '			jQuery("#' . $this->id . '_edit").removeClass("hidden");';
			$script[] = '		} else {';
			$script[] = '			jQuery("#' . $this->id . '_edit").addClass("hidden");';
			$script[] = '		}';
		}

		if ($allowClear)
		{
			$script[] = '		jQuery("#' . $this->id . '_clear").removeClass("hidden");';
		}

		$script[] = '		jQuery("#menuSelect' . $this->id . 'Modal").modal("hide");';

		if ($this->required)
		{
			$script[] = '		document.formvalidator.validate(document.getElementById("' . $this->id . '_id"));';
			$script[] = '		document.formvalidator.validate(document.getElementById("' . $this->id . '_name"));';
		}

		$script[] = '	}';

		// Edit button script
		$script[] = '	function jEditMenu_' . $value . '(title) {';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';
		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			if (isset($this->element['language']))
			{
				$clearField = JText::_('COM_MENUS_SELECT_A_MENUITEM', true);
			}
			elseif ((string) $this->element->option['value'] == '')
			{
				$clearField =  JText::_($this->element->option, true);
			}
			else
			{
				$clearField = JText::_('JDEFAULT', true);
			}

			$clearField = htmlspecialchars($clearField, ENT_QUOTES, 'UTF-8');

			$script[] = '	function jClearMenu(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "' . $clearField . '";';
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

		$linkItems = 'index.php?option=com_menus&amp;view=items&amp;layout=modal&amp;tmpl=component'
			. '&amp;function=jSelectMenu_' . $this->id;

		$linkItem  = 'index.php?option=com_menus&amp;view=item&amp;layout=modal&amp;tmpl=component'
			. '&amp;task=item.edit'
			. '&amp;function=jEditMenu_' . $value;

		if (isset($this->element['language']))
		{
			$linkItems .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkItem  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle = JText::_('COM_MENUS_CHANGE_MENUITEM') . ' (' . $this->element['label'] . ')';
		}
		else
		{
			$modalTitle = JText::_('COM_MENUS_CHANGE_MENUITEM');
		}

		$urlSelect = $linkItems . '&amp;' . JSession::getFormToken() . '=1';
		$urlEdit   = $linkItem . '&amp;id=' . $value . '&amp;' . JSession::getFormToken() . '=1';

		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__menu'))
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
			if (isset($this->element['language']))
			{
				$title = JText::_('COM_MENUS_SELECT_A_MENUITEM', true);
			}
			elseif ((string) $this->element->option['value'] == '')
			{
				$title = JText::_($this->element->option, true);
			}
			else
			{
				$title = JText::_('JDEFAULT');
			}
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current menuitem display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select menu item button
		$html[] = '<a'
			. ' class="btn hasTooltip"'
			. ' data-toggle="modal"'
			. ' role="button"'
			. ' href="#menuSelect' . $this->id . 'Modal"'
			. ' title="' . JHtml::tooltipText('COM_MENUS_CHANGE_MENUITEM') . '">'
			. '<span class="icon-file"></span> ' . JText::_('JSELECT')
			. '</a>';

		// Edit menuitem button
		if ($allowEdit)
		{
			$html[] = '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#menuEdit' . $value . 'Modal"'
				. ' title="' . JHtml::tooltipText('COM_MENUS_EDIT_MENUITEM') . '">'
				. '<span class="icon-edit"></span> ' . JText::_('JACTION_EDIT')
				. '</a>';
		}

		// Clear menu button
		if ($allowClear)
		{
			$html[] = '<button'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' onclick="return jClearMenu(\'' . $this->id . '\')">'
				. '<span class="icon-remove"></span>' . JText::_('JCLEAR')
				. '</button>';
		}

		$html[] = '</span>';

		// Select menuitem modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'menuSelect' . $this->id . 'Modal',
			array(
				'title'       => $modalTitle,
				'url'         => $urlSelect,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
						. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>',
			)
		);

		// Edit menuitem modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'menuEdit' . $value . 'Modal',
			array(
				'title'       => JText::_('COM_MENUS_EDIT_MENUITEM'),
				'backdrop'    => 'static',
				'keyboard'    => false,
				'closeButton' => false,
				'url'         => $urlEdit,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"'
						. ' onclick="jQuery(\'#menuEdit' . $value . 'Modal iframe\').contents().find(\'#closeBtn\').click();">'
						. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
						. '<button type="button" class="btn btn-primary" aria-hidden="true"'
						. ' onclick="jQuery(\'#menuEdit' . $value . 'Modal iframe\').contents().find(\'#saveBtn\').click();">'
						. JText::_("JSAVE") . '</button>'
						. '<button type="button" class="btn btn-success" aria-hidden="true"'
						. ' onclick="jQuery(\'#menuEdit' . $value . 'Modal iframe\').contents().find(\'#applyBtn\').click();">'
						. JText::_("JAPPLY") . '</button>',
			)
		);

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
