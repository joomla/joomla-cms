<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal newsfeeds picker.
 *
 * @since  1.6
 */
class JFormFieldModal_Newsfeed extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   1.6
	 */
	protected $type = 'Modal_Newsfeed';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$allowEdit  = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language
		JFactory::getLanguage()->load('com_newsfeeds', JPATH_ADMINISTRATOR);

		// The active newsfeed id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Build the script.
		$script = array();

		// Select button script
		$script[] = '	function jSelectNewsfeed_' . $this->id . '(id, name, object) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = name;';

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

		$script[] = '		jQuery("#newsfeedSelect' . $this->id . 'Modal").modal("hide");';

		if ($this->required)
		{
			$script[] = '		document.formvalidator.validate(document.getElementById("' . $this->id . '_id"));';
			$script[] = '		document.formvalidator.validate(document.getElementById("' . $this->id . '_name"));';
		}

		$script[] = '	}';

		// Edit button script
		$script[] = '	function jEditNewsfeed_' . $value . '(name) {';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = name;';
		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearNewsfeed(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "'
				. htmlspecialchars(JText::_('COM_NEWSFEEDS_SELECT_A_FEED', true), ENT_COMPAT, 'UTF-8') . '";';
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

		$linkNewsfeeds = 'index.php?option=com_newsfeeds&amp;view=newsfeeds&amp;layout=modal&amp;tmpl=component'
			. '&amp;function=jSelectNewsfeed_' . $this->id;

		$linkNewsfeed  = 'index.php?option=com_newsfeeds&amp;view=newsfeed&amp;layout=modal&amp;tmpl=component'
			. '&amp;task=newsfeed.edit'
			. '&amp;function=jEditNewsfeed_' . $value;

		if (isset($this->element['language']))
		{
			$linkNewsfeeds .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkNewsfeed  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle     = JText::_('COM_NEWSFEEDS_CHANGE_FEED') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle     = JText::_('COM_NEWSFEEDS_CHANGE_FEED');
		}

		$urlSelect = $linkNewsfeeds . '&amp;' . JSession::getFormToken() . '=1';
		$urlEdit   = $linkNewsfeed . '&amp;id=' . $value . '&amp;' . JSession::getFormToken() . '=1';

		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('name'))
				->from($db->quoteName('#__newsfeeds'))
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
			$title = JText::_('COM_NEWSFEEDS_SELECT_A_FEED');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current newsfeed display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select newsfeed button
		$html[] = '<a'
			. ' class="btn hasTooltip"'
			. ' data-toggle="modal"'
			. ' role="button"'
			. ' href="#newsfeedSelect' . $this->id . 'Modal"'
			. ' title="' . JHtml::tooltipText('COM_NEWSFEEDS_CHANGE_FEED') . '">'
			. '<span class="icon-file"></span> ' . JText::_('JSELECT')
			. '</a>';

		// Edit newsfeed button
		if ($allowEdit)
		{
			$html[] = '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#newsfeedEdit' . $value . 'Modal"'
				. ' title="' . JHtml::tooltipText('COM_NEWSFEEDS_EDIT_NEWSFEED') . '">'
				. '<span class="icon-edit"></span> ' . JText::_('JACTION_EDIT')
				. '</a>';
		}

		// Clear newsfeed button
		if ($allowClear)
		{
			$html[] = '<button'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' onclick="return jClearNewsfeed(\'' . $this->id . '\')">'
				. '<span class="icon-remove"></span>' . JText::_('JCLEAR')
				. '</button>';
		}

		$html[] = '</span>';

		// Select newsfeed modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'newsfeedSelect' . $this->id . 'Modal',
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

		// Edit newsfeed modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'newsfeedEdit' . $value . 'Modal',
			array(
				'title'       => JText::_('COM_NEWSFEEDS_EDIT_NEWSFEED'),
				'backdrop'    => 'static',
				'keyboard'    => false,
				'closeButton' => false,
				'url'         => $urlEdit,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<a type="button" class="btn" data-dismiss="modal" aria-hidden="true"'
						. ' onclick="jQuery(\'#newsfeedEdit' . $value . 'Modal iframe\').contents().find(\'#closeBtn\').click();">'
						. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>'
						. '<button type="button" class="btn btn-primary" aria-hidden="true"'
						. ' onclick="jQuery(\'#newsfeedEdit' . $value . 'Modal iframe\').contents().find(\'#saveBtn\').click();">'
						. JText::_("JSAVE") . '</button>'
						. '<button type="button" class="btn btn-success" aria-hidden="true"'
						. ' onclick="jQuery(\'#newsfeedEdit' . $value . 'Modal iframe\').contents().find(\'#applyBtn\').click();">'
						. JText::_("JAPPLY") . '</button>',
			)
		);

		// Add class='required' for client side validation
		$class = $this->required ? ' class="required modal-value"' : '';

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.4
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
