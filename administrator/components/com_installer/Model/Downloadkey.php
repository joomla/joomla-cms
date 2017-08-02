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
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;

/**
 * Download key model
 *
 * @since  __DEPLOY_VERSION__
 */
class Downloadkey extends Admin
{
	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $typeAlias = 'com_installer.downloadkey';

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
		\JForm::addFieldPath('JPATH_ADMINISTRATOR/components/com_users/models/fields');

		// Get the form.
		$form = $this->loadForm('com_installer.downloadkey', 'downloadkey', array('control' => 'jform', 'load_data' => $loadData));

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

		$this->preprocessData('com_installer.downloadkey', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  \JObject|boolean  Object on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		$db  = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'e.type',
						's.extra_query',
						'e.element',
						'e.folder',
						'e.client_id'
					)
				)
			)
			->from('#__update_sites AS s')
			->innerJoin('#__update_sites_extensions AS se ON (se.update_site_id = s.update_site_id)')
			->innerJoin('#__extensions AS e ON (e.extension_id = se.extension_id)')
			->where('s.update_site_id' . ' = ' . $item->update_site_id);
		$db->setQuery($query);
		$extension = $db->loadObject();

		$downloadKey = InstallerHelper::getDownloadKey($extension);

		$app = \JFactory::getApplication();
		$app->setUserState('prefix', $downloadKey['prefix']);
		$app->setUserState('suffix', $downloadKey['dlidsuffix']);

		$item->extra_query = $downloadKey['value'];

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save($data)
	{
		$app = \JFactory::getApplication();
		$prefix = $app->getUserState('prefix');
		$suffix = $app->getUserState('suffix');

		$data['extra_query'] = $prefix . $data['extra_query'] . $suffix;

		return parent::save($data);
	}
}
