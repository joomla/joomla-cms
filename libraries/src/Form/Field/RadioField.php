<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides radio button inputs
 *
 * @link   http://www.w3.org/TR/html-markup/command.radio.html#command.radio
 * @since  1.7.0
 */
class RadioField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Radio';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $layout = 'joomla.form.field.radio.buttons';

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$extraData = array(
			'options' => $this->getOptions(),
			'value'   => (string) $this->value,
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLabel()
	{
		// Switcher uses <legend> element inside field layout as label.
		if ($this->layout === 'joomla.form.field.radio.switcher')
		{
			return '';
		}

		return parent::getLabel();
	}

	/**
	 * Method to render field with label and input.
	 *
	 * @param   array  $options  Options to be passed into the rendering of the field
	 *
	 * @return  string  A string containing the HTML of the field
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function renderField($options = [])
	{
		// Switcher uses <legend> element inside field layout as label.
		if ($this->layout === 'joomla.form.field.radio.switcher')
		{
			$options['hiddenLabel'] = true;
		}

		return parent::renderField($options);
	}
}
