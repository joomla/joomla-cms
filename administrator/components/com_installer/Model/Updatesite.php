<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Model\Admin;

/**
 * Item Model for a Contact.
 *
 * @since  __DEPLOY_VERSION__
 */
class Updatesite extends Admin
{
	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $typeAlias = 'com_installer.updatesite';

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		\JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_users/models/fields');

		// Get the form.
		$form = $this->loadForm('com_installer.updatesite', 'updatesite', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		$data = $this->getItem();

		$this->preprocessData('com_installer.updatesite', $data);

		return $data;
	}
}
