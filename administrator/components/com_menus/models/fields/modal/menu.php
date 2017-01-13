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
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		$allowNew    = ((string) $this->element['new'] == 'true');
		$allowEdit   = ((string) $this->element['edit'] == 'true');
		$allowClear  = ((string) $this->element['clear'] != 'false');
		$allowSelect = ((string) $this->element['select'] != 'false');
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
		if ($allowSelect)
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

		$title = empty($title) ? JText::_('COM_MENUS_SELECT_A_MENUITEM') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current menu item display field.
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select menu item button
		if ($allowSelect)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalSelect' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_MENUS_CHANGE_MENUITEM') . '">'
				. '<span class="icon-file"></span> ' . JText::_('JSELECT')
				. '</a>';
		}

		// New menu item button
		if ($allowNew)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalNew' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_MENUS_NEW_MENUITEM') . '">'
				. '<span class="icon-new"></span> ' . JText::_('JACTION_CREATE')
				. '</a>';
		}

		// Edit menu item button
		if ($allowEdit)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalEdit' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_MENUS_EDIT_MENUITEM') . '">'
				. '<span class="icon-edit"></span> ' . JText::_('JACTION_EDIT')
				. '</a>';
		}

		// Clear menu item button
		if ($allowClear)
		{
			$html .= '<a'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' href="#"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-remove"></span>' . JText::_('JCLEAR')
				. '</a>';
		}

		$html .= '</span>';

		// Select menu item modal
		if ($allowSelect)
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
		if ($allowNew)
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
		if ($allowEdit)
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

		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(JText::_('COM_MENUS_SELECT_A_MENUITEM', true), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '" />';

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
