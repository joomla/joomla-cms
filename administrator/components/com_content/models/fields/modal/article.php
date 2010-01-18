<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports a modal article picker.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class JFormFieldModal_Article extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Modal_Article';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getInput()
	{
		// Initialise variables.
		$document	= JFactory::getDocument();
		$db			= JFactory::getDBO();

		// Load the modal behavior.
		JHtml::_('behavior.modal', 'a.modal');

		// Add the JavaScript select function to the document head.
		$document->addScriptDeclaration(
		"function jSelectArticle_".$this->inputId."(id, title, catid, object) {
			document.id('".$this->inputId."_id').value = id;
			document.id('".$this->inputId."_name').value = title;
			SqueezeBox.close();
		}"
		);

		// Setup variables for display.
		$html	= array();
		$link	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_'.$this->inputId;

		$db->setQuery(
			'SELECT title' .
			' FROM #__content' .
			' WHERE id = '.(int) $this->value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}

		if (empty($title)) {
			$title = JText::_('Content_Select_an_article');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="'.$this->inputId.'_name" value="'.$title.'" disabled="disabled" />';
		$html[] = '</div>';

		// The user select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '    <a class="modal" title="'.JText::_('Content_Change_Article').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('Content_Change_Article_button').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		// The active user id field.
		$html[] = '<input type="hidden" id="'.$this->inputId.'_id" name="'.$this->inputName.'" value="'.(int)$this->value.'" />';

		return implode("\n", $html);
	}
}