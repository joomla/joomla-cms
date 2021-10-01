<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormField;

/**
 * Form Field class for the Joomla Platform.
 * Provides an input field for files
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#file-upload-state-(type=file)
 * @since  1.7.0
 */
class FileField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'File';

	/**
	 * The accepted file type list.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $accept;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $layout = 'joomla.form.field.file';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'accept':
				return $this->accept;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'accept':
				$this->accept = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @since   3.2
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->accept = (string) $this->element['accept'];
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     \Joomla\CMS\Form\Field\MediaField
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   3.6
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$extraData = array(
			'accept'   => $this->accept,
			'multiple' => $this->multiple,
		);

		return array_merge($data, $extraData);
	}
}
