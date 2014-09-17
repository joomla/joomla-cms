<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

JFormHelper::loadFieldClass('imagelist');

/**
 * Form Field class for the FOF framework
 * Media selection field.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldImagelist extends JFormFieldImageList implements FOFFormField
{
	protected $static;

	protected $repeatable;

	/** @var   FOFTable  The item being rendered in a repeatable form field */
	public $item;

	/** @var int A monotonically increasing number, denoting the row number in a repeatable view */
	public $rowid;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'static':
				if (empty($this->static))
				{
					$this->static = $this->getStatic();
				}

				return $this->static;
				break;

			case 'repeatable':
				if (empty($this->repeatable))
				{
					$this->repeatable = $this->getRepeatable();
				}

				return $this->repeatable;
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getStatic()
	{
		$imgattr = array(
			'id' => $this->id
		);

		if ($this->element['class'])
		{
			$imgattr['class'] = (string) $this->element['class'];
		}

		if ($this->element['style'])
		{
			$imgattr['style'] = (string) $this->element['style'];
		}

		if ($this->element['width'])
		{
			$imgattr['width'] = (string) $this->element['width'];
		}

		if ($this->element['height'])
		{
			$imgattr['height'] = (string) $this->element['height'];
		}

		if ($this->element['align'])
		{
			$imgattr['align'] = (string) $this->element['align'];
		}

		if ($this->element['rel'])
		{
			$imgattr['rel'] = (string) $this->element['rel'];
		}

		if ($this->element['alt'])
		{
			$alt = JText::_((string) $this->element['alt']);
		}
		else
		{
			$alt = null;
		}

		if ($this->element['title'])
		{
			$imgattr['title'] = JText::_((string) $this->element['title']);
		}

		$path = (string) $this->element['directory'];
		$path = trim($path, '/' . DIRECTORY_SEPARATOR);

		if ($this->value && file_exists(JPATH_ROOT . '/' . $path . '/' . $this->value))
		{
			$src = FOFPlatform::getInstance()->URIroot() . '/' . $path . '/' . $this->value;
		}
		else
		{
			$src = '';
		}

		return JHtml::image($src, $alt, $imgattr);
	}

	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getRepeatable()
	{
		return $this->getStatic();
	}
}
