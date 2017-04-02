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

JFormHelper::loadFieldClass('list');

/**
 * Generates the list of directories  available for drag and drop upload.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       __DEPLOY_VERSION__
 */
class JFormFieldUploaddirs extends JFormFieldList
{
	protected $type = 'uploaddirs';

	/**
	 * Method to get the directories options.
	 *
	 * @return  array  The skins option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		$options = array();
		$root    = JComponentHelper::getParams('com_media')->get('image_path');


		$directories = glob(JPATH_ROOT . '/' . $root . '/*', GLOB_ONLYDIR);

		$options[] = JHtml::_('select.option', '', '');

		for ($i = 0; $i < count($directories); ++$i)
		{
			$dir = basename($directories[$i]);
			$options[] = JHtml::_('select.option', $i, $dir);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
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
