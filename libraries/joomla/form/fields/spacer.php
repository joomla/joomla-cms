<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides spacer markup to be used in form layouts.
 *
 * @since  1.7.0
 */
class JFormFieldSpacer extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Spacer';

	/**
	 * Method to get the field input markup for a spacer.
	 * The spacer does not have accept input.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		return ' ';
	}

	/**
	 * Method to get the field label markup for a spacer.
	 * Use the label text or name from the XML element as the spacer or
	 * Use a hr="true" to automatically generate plain hr markup
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   1.7.0
	 */
	protected function getLabel()
	{
		$html = array();
		$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$html[] = '<span class="spacer">';
		$html[] = '<span class="before"></span>';
		$html[] = '<span' . $class . '>';

		if ((string) $this->element['hr'] == 'true')
		{
			$html[] = '<hr' . $class . ' />';
		}
		else
		{
			$label = '';

			// Get the label text from the XML element, defaulting to the element name.
			$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
			$text = $this->translateLabel ? JText::_($text) : $text;

			// Build the class for the label.
			$class = !empty($this->description) ? 'hasPopover' : '';
			$class = $this->required == true ? $class . ' required' : $class;

			// Add the opening label tag and main attributes attributes.
			$label .= '<label id="' . $this->id . '-lbl" class="' . $class . '"';

			// If a description is specified, use it to build a tooltip.
			if (!empty($this->description))
			{
				JHtml::_('bootstrap.popover');
				$label .= ' title="' . htmlspecialchars(trim($text, ':'), ENT_COMPAT, 'UTF-8') . '"';
				$label .= ' data-content="' . htmlspecialchars(
					$this->translateDescription ? JText::_($this->description) : $this->description,
					ENT_COMPAT,
					'UTF-8'
				) . '"';

				if (JFactory::getLanguage()->isRtl())
				{
					$label .= ' data-placement="left"';
				}
			}

			// Add the label text and closing tag.
			$label .= '>' . $text . '</label>';
			$html[] = $label;
		}

		$html[] = '</span>';
		$html[] = '<span class="after"></span>';
		$html[] = '</span>';

		return implode('', $html);
	}

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 *
	 * @since   1.7.0
	 */
	protected function getTitle()
	{
		return $this->getLabel();
	}

	/**
	 * Method to get a control group with label and input.
	 *
	 * @param   array  $options  Options to be passed into the rendering of the field
	 *
	 * @return  string  A string containing the html for the control group
	 *
	 * @since   3.7.3
	 */
	public function renderField($options = array())
	{
		$options['class'] = empty($options['class']) ? 'field-spacer' : $options['class'] . ' field-spacer';

		return parent::renderField($options);
	}
}
