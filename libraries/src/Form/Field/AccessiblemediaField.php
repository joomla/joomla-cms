<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Field\SubformField;

/**
 * The Field to load the form inside current form
 *
 * @since  4.0.0
 */
class AccessiblemediaField extends SubformField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $type = 'Accessiblemedia';

	/**
	 * The preview.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $preview;

	/**
	 * The directory.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $directory;

	/**
	 * The previewWidth.
	 *
	 * @var    integer
	 * @since  4.0.0
	 */
	protected $previewWidth;

	/**
	 * The previewHeight.
	 *
	 * @var    integer
	 * @since  4.0.0
	 */
	protected $previewHeight;

	/**
	 * Layout to render
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $layout;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   4.0.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'directory':
			case 'preview':
			case 'previewHeight':
			case 'previewWidth':
				return $this->$name;
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
	 * @since   4.0.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'directory':
			case 'preview':
				$this->$name = (string) $value;
				break;

			case 'previewHeight':
			case 'previewWidth':
				$this->$name = (int) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		json_decode($value);

		// Check if value is a valid JSON string.
		if ($value !== '' && json_last_error() !== JSON_ERROR_NONE)
		{
			/**
			* If the value is not empty and is not a valid JSON string,
			* it is most likely a custom field created in Joomla 3 and
			* the value is a string that contains the file name.
			*/
			if (file_exists(JPATH_ROOT . '/' . $value))
			{
				$value = '{"imagefile":"' . $value . '","alt_text":""}';
			}
			else
			{
				$value = '';
			}
		}

		if (!parent::setup($element, $value, $group))
		{
			return false;
		}

		$this->directory = (string) $this->element['directory'];
		$this->preview = (string) $this->element['preview'];
		$this->previewHeight = isset($this->element['preview_height']) ? (int) $this->element['preview_height'] : 200;
		$this->previewWidth = isset($this->element['preview_width']) ? (int) $this->element['preview_width'] : 200;

		$xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<form>
	<fieldset 
		name="accessiblemedia"
		label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_LABEL"
	>
		<field
			name="imagefile"
			type="media"
			label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_IMAGEFILE_LABEL"
			directory="$this->directory"
			preview="$this->preview"
			preview_width="$this->previewWidth"
			preview_height="$this->previewHeight"
		/>

		<field
			name="alt_text"
			type="text"
			label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_ALT_TEXT_LABEL"
		/>
	</fieldset>
</form>
XML;
		$this->formsource = $xml;

		$this->layout = 'joomla.form.field.media.accessiblemedia';

		return true;
	}
}
