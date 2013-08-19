<?php
/**
 * Display a json loaded window with a repeatable set of sub fields
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Display a json loaded window with a repeatable set of sub fields
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldRepeatable extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Repeatable';

	/**
	 * Method to get the field input markup.
	 *
	 * @since	1.6
	 *
	 * @return	string	The field input markup.
	 */

	protected function getInput()
	{
		// Initialize variables.
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$options = array();
		$subForm = new JForm($this->name, array('control' => 'jform'));
		$xml = $this->element->children()->asXML();
		$subForm->load($xml);

		// Needed for repeating modals in gmaps
		$subForm->repeatCounter = (int) @$this->form->repeatCounter;
		$input = $app->input;
		$view = $input->get('view', 'item');
		$children = $this->element->children();

		// This fires a strict standard warning deep in JForm, suppress error for now
		@$subForm->setFields($children);

		$modalid = $this->id . '_modal';

		$str = array();
		$str[] = '<div id="' . $modalid . '" style="display:none">';
		$str[] = '<table class="adminlist ' . $this->element['class'] . ' table table-striped">';
		$str[] = '<thead><tr class="row0">';
		$names = array();
		$attributes = $this->element->attributes();

		foreach ($subForm->getFieldset($attributes->name . '_modal') as $field)
		{
			$names[] = (string) $field->element->attributes()->name;
			$str[] = '<th>' . strip_tags($field->getLabel($field->name));
			$str[] = '<br /><small style="font-weight:normal">' . JText::_($field->description) . '</small>';
			$str[] = '</th>';
		}

		$str[] = '<th><a href="#" class="add btn button btn-success"><i class="icon-plus"></i> </a></th>';
		$str[] = '</tr></thead>';
		$str[] = '<tbody><tr>';

		foreach ($subForm->getFieldset($attributes->name . '_modal') as $field)
		{
			$str[] = '<td>' . $field->getInput() . '</td>';
		}

		$str[] = '<td>';
		$str[] = '<div class="btn-group"><a class="add btn button btn-success"><i class="icon-plus"></i> </a>';
		$str[] = '<a class="remove btn button btn-danger"><i class="icon-minus"></i> </a></div>';
		$str[] = '</td>';
		$str[] = '</tr></tbody>';
		$str[] = '</table>';
		$str[] = '</div>';

		static $modalrepeat;

		if (!isset($modalrepeat))
		{
			$modalrepeat = array();
		}

		if (!array_key_exists($modalid, $modalrepeat))
		{
			$modalrepeat[$modalid] = array();
		}

		if (!isset($this->form->repeatCounter))
		{
			$this->form->repeatCounter = 0;
		}

		if (!array_key_exists($this->form->repeatCounter, $modalrepeat[$modalid]))
		{
			// If loaded as js template then we don't want to repeat this again
			$names = json_encode($names);

			$modalrepeat[$modalid][$this->form->repeatCounter] = true;
			$script = str_replace('-', '', $modalid) . " = new ModalRepeat('$modalid', $names, '$this->id');";
			$option = $input->get('option');

			$context = strtoupper($option);
			if ($context === 'COM_ADVANCEDMODULES')
			{
				$context = 'COM_MODULES';
			}

			$script .= "window.addEvent('domready', function() {
				var a = jQuery(\"a:contains('" . $context . '_' . str_replace('jform_params_', '', $modalid) . '_FIELDSET_LABEL' . "')\");
				if (a.length > 0) {
					a = a[0];
					var href= a.get('href');
					jQuery(href)[0].destroy();

					var accord = a.getParent('.accordion-group');
					if (typeOf(accord) !== 'null') {
						accord.destroy();
					} else {
						a.destroy();
					}
					" . $script . "
				}
			});";

			$document = JFactory::getDocument();

			// Define the close lang string
			JText::script('JTOOLBAR_CLOSE');

			// Won't work when rendering in Admin Module page			
			JHtml::_('behavior.framework');
			$document->addScript(JUri::root() . 'media/cms/js/repeatable.js');

			$document->addScriptDeclaration( $script );
		}

		$close = "function(c){" . $modalid . ".onClose(c);}";
		$icon = $this->element['icon'] ? '<i class="icon-' . $this->element['icon'] . '"></i> ' : '';
		$str[] = '<button class="btn" id="' . $modalid . '_button" data-modal="' . $modalid . '">' . $icon . JText::_('JLIB_FORM_BUTTON_SELECT') . '</button>';

		if (is_array($this->value))
		{
			$this->value = array_shift($this->value);
		}

		$value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
		$str[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $value . '" />';

		return implode("\n", $str);
	}
}
