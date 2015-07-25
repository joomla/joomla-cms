<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * Field to load a subform
 *
 * @Example:
 * 	<field name="field-name" type="subform"
 * 		formsource="path/to/form.xml" max="3"
 * 		layout="joomla.form.field.subform.repeatable-table" component="com_zcm" client="site"
 * 		label="Field Label" description="Field Description" />
 *
 */
class JFormFieldSubform extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Subform';

	/**
	 * Form source
	 * @var string
	 */
	protected $formsource;

	/**
	 * Minimum items in repeat mode
	 * @var int
	 */
	protected $min = 0;

	/**
	 * Maximum items in repeat mode
	 * @var int
	 */
	protected $max = 1000;

	/**
	 * Layout to render the form
	 * @var  string
	 */
	protected $layout = 'joomla.form.field.subform.repeatable';

	/**
	 * Whether group subform fields by its fieldset
	 * @var boolean
	 */
	protected $groupByFieldset = false;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value.
	 *
	 * @return  boolean  True on success.
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if(!parent::setup($element, $value, $group))
		{
			return false;
		}
		// Get the form source
		$this->formsource = (string) $this->element['formsource'];
		// Add root path if we have a path to XML file
		if(strrpos($this->formsource, '.xml') === strlen($this->formsource) - 4)
		{
			$this->formsource = JPath::clean(JPATH_ROOT . '/' . $this->formsource);
		}

		if($this->element['min'])
		{
			$this->min = (int) $this->element['min'];
		}

		if($this->element['max'])
		{
			$this->max = max(1, (int) $this->element['max']);
		}

		if($this->element['layout'])
		{
			$this->layout = (string) $this->element['layout'];
		}

		$this->groupByFieldset = $this->element['groupByFieldset'] && in_array((string) $this->element['groupByFieldset'], array('true', '1'));

		return true;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$value = $this->value ? $this->value : array();
		if($value && is_string($value))
		{
			// Guess there the json_encoded value
			$value = json_decode($value, true);
		}

		// Prepare render data
		$data = array();
		$tmpl = null;
		$forms = array();
		$control = $this->name;

		try
		{
			// Get the form template
			$formname = 'subform' . ($this->group ? $this->group . '.' : '.') . $this->fieldname;
			$tmplcontrol = !$this->multiple ? $control : $control . '[' . $this->fieldname . '0]';
			$tmpl = JForm::getInstance($formname, $this->formsource, array('control' => $tmplcontrol));

			// Prepare the forms for exiting values
			if($this->multiple)
			{
				$value = array_values($value);
				$c = max($this->min, min(count($value), $this->max));
				for($i = 0; $i < $c; $i++){
					$itemcontrol = $control . '[' . $this->fieldname . $i . ']';
					$itemform = JForm::getInstance($formname.$i, $this->formsource, array('control' => $itemcontrol));
					if(!empty($value[$i]))
					{
						$itemform->bind($value[$i]);
					}
					$forms[] = $itemform;
				}
			}
			else
			{
				$tmpl->bind($value);
				$forms[] = $tmpl;
			}
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		// Show buttons
		$buttons_str = (string) $this->element['buttons'];
		if($buttons_str)
		{
			$buttons = explode(',', $buttons_str);
			$buttons = array_fill_keys($buttons, true);
			$buttons = array_merge(array('add' => false, 'remove' => false, 'move' => false), $buttons);
		}
		else
		{
			$buttons = array('add' => true, 'remove' => true, 'move' => true);
		}

		$data['tmpl'] = $tmpl;
		$data['forms'] = $forms;
		$data['multiple'] = $this->multiple;
		$data['min'] = $this->min;
		$data['max'] = $this->max;
		$data['fieldname'] = $this->fieldname;
		$data['control'] = $control;
		$data['buttons'] = $buttons;

		$label = (string) $this->element['label'];
		$data['label'] = $this->translateLabel ? JText::_($label) : $label;
		$data['description'] = $this->translateLabel ? JText::_($this->description) : $this->description;
		$data['groupByFieldset'] = $this->groupByFieldset;

		$client = $this->element['client'] ? (string) $this->element['client'] : 'site';
		$component = $this->element['component'] ? (string) $this->element['component'] : 'auto';

		// Render
		$html = JLayoutHelper::render($this->layout, $data, null, array('client' => $client, 'component' => $component));

		// Add hidden input on the front of subform inputs, in multiple mode
		// for allow to submit the empty value
		// @TODO: Remove when https://github.com/joomla/joomla-cms/pull/7381 will be fixed
		if($this->multiple)
		{
			$html = '<input name="' . $this->name . '" type="hidden" value="" />' . $html;
		}

		return $html;
	}

	/**
	 * Method to get the name used for the field input tag.
	 *
	 * @param   string  $fieldName  The field element name.
	 *
	 * @return  string  The name to be used for the field input tag.
	 */
	protected function getName($fieldName)
	{
		$name = '';

		// If there is a form control set for the attached form add it first.
		if ($this->formControl)
		{
			$name .= $this->formControl;
		}

		// If the field is in a group add the group control to the field name.
		if ($this->group)
		{
			// If we already have a name segment add the group control as another level.
			$groups = explode('.', $this->group);

			if ($name)
			{
				foreach ($groups as $group)
				{
					$name .= '[' . $group . ']';
				}
			}
			else
			{
				$name .= array_shift($groups);

				foreach ($groups as $group)
				{
					$name .= '[' . $group . ']';
				}
			}
		}

		// If we already have a name segment add the field name as another level.
		if ($name)
		{
			$name .= '[' . $fieldName . ']';
		}
		else
		{
			$name .= $fieldName;
		}

		return $name;
	}

}
