<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.editor');
jimport('joomla.form.field');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldEditor extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Editor';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		// editor attribute can be in the form of:
		// editor="desired|alternative"
		if ($editorName = trim($this->_element->attributes('editor')))
		{
			$parts	= explode('|', $editorName);
			$db		= &JFactory::getDbo();
			$query	= 'SELECT element' .
					' FROM #__plugins' .
					' WHERE element	= '.$db->Quote($parts[0]) .
					'  AND folder = '.$db->Quote('editors') .
					'  AND published = 1';
			$db->setQuery($query);
			if ($db->loadResult()) {
				$editorName	= $parts[0];
			}
			else if (isset($parts[1])) {
				$editorName	= $parts[1];
			}
			else {
				$editorName	= '';
			}
			$this->_element->addAttribute('editor', $editorName);
		}
		$editor		= &JFactory::getEditor($editorName ? $editorName : null);
		$rows		= $this->_element->attributes('rows');
		$cols		= $this->_element->attributes('cols');
		$height		= ($this->_element->attributes('height')) ? $this->_element->attributes('height') : '200';
		$width		= ($this->_element->attributes('width')) ? $this->_element->attributes('width') : '100%';
		$class		= ($this->_element->attributes('class') ? 'class="'.$this->_element->attributes('class').'"' : 'class="text_area"');
		$buttons	= $this->_element->attributes('buttons');

		$editor->set('TemplateXML',	$this->_element->attributes('templatexml'));
		if ($buttons == 'true') {
			$buttons	= true;
		} else {
			$buttons	= explode(',', $buttons);
		}
		// convert <br /> tags so they are not visible when editing
		//$value	= str_replace('<br />', "\n", $value);

		return $editor->display($this->inputName, htmlspecialchars($this->value), $width, $height, $cols, $rows, $buttons);
	}

	public function render(&$xml, $value, $formName, $groupName)
	{
		$result		= &parent::render($xml, $value, $formName, $groupName);
		$editorName	= trim($this->_element->attributes('editor')) ? trim($this->_element->attributes('editor')) : null;
		$result->editor	= &JFactory::getEditor($editorName);
		return $result;
	}
}