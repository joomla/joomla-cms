<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla CMS.
 * Provides a modal link editor
 *
 * @since  3.7
 */
class JFormFieldLink extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $type = 'Link';

	/**
	 * The authorField.
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $authorField;

	/**
	 * The asset.
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $asset;

	/**
	 * The link.
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $link;

	/**
	 * Modal width.
	 *
	 * @var    integer
	 * @since  3.7
	 */
	protected $width;

	/**
	 * Modal height.
	 *
	 * @var    integer
	 * @since  3.7
	 */
	protected $height;

	/**
	 * Layout to render
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $layout = 'joomla.form.field.link';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.7
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'authorField':
			case 'asset':
			case 'link':
			case 'width':
			case 'height':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.7
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'authorField':
			case 'asset':
			case 'link':
			case 'width':
			case 'height':
				$this->$name = (string) $value;
				break;
			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see 	JFormField::setup()
	 * @since   3.7
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result == true)
		{
			$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';

			$this->authorField   = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
			$this->asset         = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];
			$this->link          = (string) $this->element['link'];
			$this->width  	     = isset($this->element['width']) ? (int) $this->element['width'] : 800;
			$this->height 	     = isset($this->element['height']) ? (int) $this->element['height'] : 260;
		}

		return $result;
	}

	/**
	 * Method to get the field input markup for a link editor.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7
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
	 * Get the data that is going to be passed to the layout
	 *
	 * @return  array
	 */
	public function getLayoutData()
	{
		// Get the basic field data
		$data = parent::getLayoutData();

		$asset = $this->asset;

		if ($asset == '')
		{
			$asset = JFactory::getApplication()->input->get('option');
		}

		$extraData = array(
			'asset'         => $asset,
			'authorField'   => $this->authorField,
			'authorId'      => $this->form->getValue($this->authorField),
			'link'          => $this->link,
		);

		return array_merge($data, $extraData);
	}
}
