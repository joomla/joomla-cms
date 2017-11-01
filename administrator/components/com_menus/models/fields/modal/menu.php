<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
JHtml::_('bootstrap.tooltip', '.hasTooltip');

/**
 * Supports a modal menu item picker.
 *
 * @since  3.7.0
 */
class JFormFieldModal_Menu extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.7.0
	 */
	protected $type = 'Modal_Menu';

	/**
	 * Determinate, if the select button is shown
	 *
	 * @var     boolean
	 * @since   3.7.0
	 */
	protected $allowSelect = true;

	/**
	 * Determinate, if the clear button is shown
	 *
	 * @var     boolean
	 * @since   3.7.0
	 */
	protected $allowClear = true;

	/**
	 * Determinate, if the create button is shown
	 *
	 * @var     boolean
	 * @since   3.7.0
	 */
	protected $allowNew = false;

	/**
	 * Determinate, if the edit button is shown
	 *
	 * @var     boolean
	 * @since   3.7.0
	 */
	protected $allowEdit = false;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.7.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'allowSelect':
			case 'allowClear':
			case 'allowNew':
			case 'allowEdit':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'allowSelect':
			case 'allowClear':
			case 'allowNew':
			case 'allowEdit':
				$value = (string) $value;
				$this->$name = !($value === 'false' || $value === 'off' || $value === '0');
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.7.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->allowSelect = ((string) $this->element['select']) !== 'false';
			$this->allowClear = ((string) $this->element['clear']) !== 'false';
			$this->allowNew = ((string) $this->element['new']) === 'true';
			$this->allowEdit = ((string) $this->element['edit']) === 'true';
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		$clientId    = (int) $this->element['clientid'];

		// Load language
		JFactory::getLanguage()->load('com_menus', JPATH_ADMINISTRATOR);

		// The active article id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'Item_' . $this->id;

		// Add the modal field script to the document head.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($this->allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				JFactory::getDocument()->addScriptDeclaration("
				function jSelectMenu_" . $this->id . "(id, title, object) {
					window.processModalSelect('Item', '" . $this->id . "', id, title, '', object);
				}
				");

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkSuffix = '&amp;layout=modal&amp;client_id=' . $clientId . '&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$linkItems  = 'index.php?option=com_menus&amp;view=items' . $linkSuffix;
		$linkItem   = 'index.php?option=com_menus&amp;view=item' . $linkSuffix;
		$modalTitle = JText::_('COM_MENUS_CHANGE_MENUITEM');

		if (isset($this->element['language']))
		{
			$linkItems  .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkItem   .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle .= ' &#8212; ' . $this->element['label'];
		}

		$urlSelect = $linkItems . '&amp;function=jSelectMenu_' . $this->id;
		$urlEdit   = $linkItem . '&amp;task=item.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
		$urlNew    = $linkItem . '&amp;task=item.add';

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

		// Placeholder if option is present or not
		if (empty($title))
 		{
			if ($this->element->option && (string) $this->element->option['value'] == '')
			{
				$title_holder = JText::_($this->element->option, true);
			}
			else
			{
				$title_holder = JText::_('COM_MENUS_SELECT_A_MENUITEM', true);
			}
		}

		$title = empty($title) ? $title_holder : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current menu item display field.
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select menu item button
		if ($this->allowSelect)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalSelect' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_MENUS_CHANGE_MENUITEM') . '">'
				. '<span class="icon-file" aria-hidden="true"></span> ' . JText::_('JSELECT')
				. '</a>';
		}

		// New menu item button
		if ($this->allowNew)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalNew' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_MENUS_NEW_MENUITEM') . '">'
				. '<span class="icon-new" aria-hidden="true"></span> ' . JText::_('JACTION_CREATE')
				. '</a>';
		}

		// Edit menu item button
		if ($this->allowEdit)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalEdit' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_MENUS_EDIT_MENUITEM') . '">'
				. '<span class="icon-edit" aria-hidden="true"></span> ' . JText::_('JACTION_EDIT')
				. '</a>';
		}

		// Clear menu item button
		if ($this->allowClear)
		{
			$html .= '<a'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' href="#"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-remove" aria-hidden="true"></span>' . JText::_('JCLEAR')
				. '</a>';
		}

		$html .= '</span>';

		// Select menu item modal
		if ($this->allowSelect)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'       => $modalTitle,
					'url'         => $urlSelect,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<a role="button" class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>',
				)
			);
		}

		// New menu item modal
		if ($this->allowNew)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalNew' . $modalId,
				array(
					'title'       => JText::_('COM_MENUS_NEW_MENUITEM'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<a role="button" class="btn" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'item\', \'cancel\', \'item-form\'); return false;">'
							. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'item\', \'save\', \'item-form\'); return false;">'
							. JText::_('JSAVE') . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'item\', \'apply\', \'item-form\'); return false;">'
							. JText::_('JAPPLY') . '</a>',
				)
			);
		}

		// Edit menu item modal
		if ($this->allowEdit)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalEdit' . $modalId,
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
					'footer'      => '<a role="button" class="btn" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'item\', \'cancel\', \'item-form\'); return false;">'
							. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'item\', \'save\', \'item-form\'); return false;">'
							. JText::_('JSAVE') . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'item\', \'apply\', \'item-form\'); return false;">'
							. JText::_('JAPPLY') . '</a>',
				)
			);
		}

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		// Placeholder if option is present or not when clearing field
		if ($this->element->option && (string) $this->element->option['value'] == '')
		{
			$title_holder = JText::_($this->element->option, true);
		}
		else
		{
			$title_holder = JText::_('COM_MENUS_SELECT_A_MENUITEM', true);
		}

		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars($title_holder, ENT_COMPAT, 'UTF-8') . '" value="' . $value . '" />';

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.7.0
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
