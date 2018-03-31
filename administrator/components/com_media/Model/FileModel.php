<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * File Model
 *
 * @since  4.0.0
 */
class FileModel extends FormModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \Joomla\CMS\Form\Form|boolean  A Form object on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		PluginHelper::importPlugin('media-action');

		// Get the form.
		$form = $this->loadForm('com_media.file', 'file', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the file information for the given path. Path must be
	 * in the format: adapter:path/to/file.extension
	 *
	 * @param   string  $path  The path to get the information from.
	 *
	 * @return  \stdClass  A object with file information
	 *
	 * @since   4.0.0
	 * @see     ApiModel::getFile()
	 */
	public function getFileInformation($path)
	{
		list($adapter, $path) = explode(':', $path, 2);

		return (new ApiModel)->getFile($adapter, $path, ['url' => true]);
	}
}
