<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\FolderlistField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Generates the list of directories  available for drag and drop upload.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       3.7.0
 */
class TemplateslistField extends FolderlistField
{
	protected $type = 'templateslist';

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
	 * @see     \Joomla\CMS\Form\FormField::setup()
	 * @since   3.7.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		// Get the path in which to search for file options.
		$this->recursive   = true;
		$this->hideDefault = true;
		$this->exclude     = 'system';
		$this->hideNone    = true;
		// hide_none="true"
		// recursive="true"
		// exclude="system"

		return $return;
	}

	/**
	 * Method to get the directories options.
	 *
	 * @return  array  The dirs option objects.
	 *
	 * @since   3.7.0
	 */
	public function getOptions()
	{
		$options = [];
		$directories = [JPATH_ROOT . '/templates', JPATH_ROOT . '/media/templates/site'];
		foreach ($directories as $directory) {
			$this->directory = $directory;
			$options = array_merge($options, parent::getOptions());
		}
		return $options;
	}

	/**
	 * Method to get the field input markup for the list of directories.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		$html = array();

		// Get the field options.
		$options = (array) $this->getOptions();

		// Reset the non selected value to null
		if ($options[0]->value === '') {
			$options[0]->value = '';
		}

		// Create a regular list.
		$html[] = HTMLHelper::_('select.genericlist', $options, $this->name, 'class="form-select"', 'value', 'text', $this->value, $this->id);

		return implode($html);
	}
}
