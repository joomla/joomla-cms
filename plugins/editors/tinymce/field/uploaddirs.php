<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');

JFormHelper::loadFieldClass('folderlist');

/**
 * Generates the list of directories  available for drag and drop upload.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       __DEPLOY_VERSION__
 */
class JFormFieldUploaddirs extends JFormFieldFolderList
{
	protected $type = 'uploaddirs';


	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		// Get the path in which to search for file options.
		$this->directory   = JComponentHelper::getParams('com_media')->get('image_path');
		$this->recursive   = true;
		$this->hideDefault = true;

		return $return;
	}

	/**
	 * Method to get the directories options.
	 *
	 * @return  array  The dirs option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		return parent::getOptions();
	}

	/**
	 * Method to get the field input markup for the list of directories.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		$html = array();

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a regular list.
		$html[] = JHtml::_('select.genericlist', $options, $this->name, '', 'value', 'text', $this->value, $this->id);

		return implode($html);
	}
}
