<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Implements a combo box field.
 *
 * @since  1.7.0
 */
class JFormFieldCombo extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Combo';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.8.0
	 */
	protected $layout = 'joomla.form.field.combo';

	/**
	 * Method to get the field input markup for a combo box field.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   3.8.0
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		// Get the field options.
		$options = $this->getOptions();

		$extraData = array(
			'options' => $options,
		);

		return array_merge($data, $extraData);
	}
}
