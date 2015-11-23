<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die();

class JFormFieldModal_Topic extends JFormField
{

	protected $type = 'Modal_Topic';

	protected function getInput ()
	{
		$allowEdit = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;
		
		// Load language
		JFactory::getLanguage()->load('com_cjforum', JPATH_ADMINISTRATOR);
		
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');
		
		// Build the script.
		$script = array();
		
		// Select button script
		$script[] = '	function jSelectTopic_' . $this->id . '(id, title, catid, object) {';
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
		
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';
		
		// Clear button script
		static $scriptClear;
		
		if ($allowClear && ! $scriptClear)
		{
			$scriptClear = true;
			
			$script[] = '	function jClearTopic(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "' .
					 htmlspecialchars(JText::_('COM_CJFORUM_SELECT_A_TOPIC', true), ENT_COMPAT, 'UTF-8') . '";';
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
		$link = 'index.php?option=com_cjforum_topics&amp;view=topics&amp;layout=modal&amp;tmpl=component&amp;function=jSelectTopic_' . $this->id;
		
		if (isset($this->element['language']))
		{
			$link .= '&amp;forcedLanguage=' . $this->element['language'];
		}
		
		$db = JFactory::getDbo();
		$db->setQuery('SELECT title' . ' FROM #__cjforum_topics' . ' WHERE id = ' . (int) $this->value);
		
		try
		{
			$title = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
		
		if (empty($title))
		{
			$title = JText::_('COM_CJFORUM_SELECT_A_TOPIC');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		
		// The active topic id field.
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}
		
		// The current topic display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
		$html[] = '<a class="modal btn hasTooltip" title="' . JHtml::tooltipText('COM_CJFORUM_CHANGE_ARTICLE') . '"  href="' . $link . '&amp;' .
				 JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('JSELECT') .
				 '</a>';
		
		// Edit topic button
		if ($allowEdit)
		{
			$html[] = '<a class="btn hasTooltip' . ($value ? '' : ' hidden') .
					 '" href="index.php?option=com_cjforum_topics&layout=modal&tmpl=component&task=topic.edit&id=' . $value .
					 '" target="_blank" title="' . JHtml::tooltipText('COM_CJFORUM_EDIT_TOPIC') . '" ><span class="icon-edit"></span> ' .
					 JText::_('JACTION_EDIT') . '</a>';
		}
		
		// Clear topic button
		if ($allowClear)
		{
			$html[] = '<button id="' . $this->id . '_clear" class="btn' . ($value ? '' : ' hidden') . '" onclick="return jClearTopic(\'' . $this->id .
					 '\')"><span class="icon-remove"></span> ' . JText::_('JCLEAR') . '</button>';
		}
		
		$html[] = '</span>';
		
		// class='required' for client side validation
		$class = '';
		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}
		
		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';
		
		return implode("\n", $html);
	}
}
