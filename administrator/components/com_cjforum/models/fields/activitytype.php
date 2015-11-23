<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldActivitytype extends JFormField
{
	public $type = 'Activitytype';

	protected function getInput()
	{
		$html = array();
		$groups = $this->getGroups();
		$excluded = $this->getExcluded();
		$link = 'index.php?option=com_cjforum&amp;view=activitytypes&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
			. (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
			. (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

		// Initialize some field attributes.
		$attr = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->required ? ' required' : '';

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal_' . $this->id);

		// Build the script.
		$script = array();
		$script[] = '	function jSelectActivitytype_' . $this->id . '(id, title) {';
		$script[] = '		var old_id = document.getElementById("' . $this->id . '_id").value;';
		$script[] = '		if (old_id != id) {';
		$script[] = '			document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '			document.getElementById("' . $this->id . '").value = title;';
		$script[] = '			document.getElementById("' . $this->id . '").className = document.getElementById("' . $this->id . '").className.replace(" invalid" , "");';
		$script[] = '			' . $this->onchange;
		$script[] = '		}';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Load the current username if available.
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_cjforum/tables');
		$table = JTable::getInstance('Activitytype', 'CjForumTable');
		
		if (is_numeric($this->value))
		{
			$table->load($this->value);
		}
		// Handle the special case for "current".
		elseif (strtoupper($this->value) == 'CURRENT')
		{
			// 'CURRENT' is not a reasonable value to be placed in the html
			$this->value = JFactory::getUser()->id;
			$table->load($this->value);
		}
		else
		{
			$table->title = JText::_('COM_CJFORUM_SELECT_A_RULE');
		}

		// Create a dummy text field with the user name.
		$html[] = '<div class="input-append">';
		$html[] = '	<input type="text" id="' . $this->id . '" value="' . htmlspecialchars($table->title, ENT_COMPAT, 'UTF-8') . '"' . ' readonly' . $attr . ' />';

		// Create the user select button.
		if ($this->readonly === false)
		{
			$html[] = '		<a class="btn btn-primary modal_' . $this->id . '" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '" href="' . $link . '"'
				. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
			$html[] = '<i class="icon-folder-open"></i></a>';
		}

		$html[] = '</div>';

		// Create the real field, hidden, that stored the user id.
		$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $this->value . '" />';

		return implode("\n", $html);
	}

	protected function getGroups()
	{
		return null;
	}

	protected function getExcluded()
	{
		return null;
	}
}