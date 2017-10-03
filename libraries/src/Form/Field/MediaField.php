<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;

/**
 * Provides a modal media selector including upload mechanism
 *
 * @since  1.6
 */
class MediaField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Media';

	/**
	 * The authorField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $authorField;

	/**
	 * The asset.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $asset;

	/**
	 * The link.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $link;

	/**
	 * Modal width.
	 *
	 * @var    integer
	 * @since  3.4.5
	 */
	protected $width;

	/**
	 * Modal height.
	 *
	 * @var    integer
	 * @since  3.4.5
	 */
	protected $height;

	/**
	 * The authorField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $preview;

	/**
	 * The preview.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $directory;

	/**
	 * The previewWidth.
	 *
	 * @var    int
	 * @since  3.2
	 */
	protected $previewWidth;

	/**
	 * The previewHeight.
	 *
	 * @var    int
	 * @since  3.2
	 */
	protected $previewHeight;

	/**
	 * Layout to render
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $layout = 'joomla.form.field.media';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
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
			case 'preview':
			case 'directory':
			case 'previewWidth':
			case 'previewHeight':
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
	 * @since   3.2
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
			case 'preview':
			case 'directory':
				$this->$name = (string) $value;
				break;

			case 'previewWidth':
			case 'previewHeight':
				$this->$name = (int) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see 	FormField::setup()
	 * @since   3.2
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result === true)
		{
			$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';

			$this->authorField   = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
			$this->asset         = $this->form->getValue($assetField) ?: (string) $this->element['asset_id'];
			$this->link          = (string) $this->element['link'];
			$this->width  	     = isset($this->element['width']) ? (int) $this->element['width'] : 800;
			$this->height 	     = isset($this->element['height']) ? (int) $this->element['height'] : 500;
			$this->preview       = (string) $this->element['preview'];
			$this->directory     = (string) $this->element['directory'];
			$this->previewWidth  = isset($this->element['preview_width']) ? (int) $this->element['preview_width'] : 200;
			$this->previewHeight = isset($this->element['preview_height']) ? (int) $this->element['preview_height'] : 200;
		}

		return $result;
	}

	/**
	 * Method to get the field input markup for a media selector.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
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

		if ($asset === '')
		{
			$asset = \JFactory::getApplication()->input->get('option');
		}

		if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
		{
			$this->folder = explode('/', $this->value);
			$this->folder = array_diff_assoc($this->folder, explode('/', ComponentHelper::getParams('com_media')->get('image_path', 'images')));
			array_pop($this->folder);
			$this->folder = implode('/', $this->folder);
		}
		elseif (file_exists(JPATH_ROOT . '/' . ComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $this->directory))
		{
			$this->folder = $this->directory;
		}
		else
		{
			$this->folder = '';
		}

		$extraData = array(
			'asset'         => $asset,
			'authorField'   => $this->authorField,
			'authorId'      => $this->form->getValue($this->authorField),
			'folder'        => $this->folder,
			'link'          => $this->link,
			'preview'       => $this->preview,
			'previewHeight' => $this->previewHeight,
			'previewWidth'  => $this->previewWidth,
		);

		return array_merge($data, $extraData);
	}
}
