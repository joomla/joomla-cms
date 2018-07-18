<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Overrides.Override
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Override Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgInstallerOverride extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Method to get com_templates model instance.
	 *
	 * @param   string  $name    The model name. Optional
	 * @param   string  $prefix  The class prefix. Optional
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel The model.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Template', $prefix = 'Administrator')
	{
		$app = Factory::getApplication();
		$model = $app->bootComponent('com_templates')->createMVCFactory($app)->createModel($name, $prefix);

		return $model;
	}

	/**
	 * Purges session array.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function purge()
	{
		// Delete stored session value.
		$session = Factory::getSession();
		$session->clear('override.beforeEventFiles');
		$session->clear('override.afterEventFiles');
	}

	/**
	 * Method to store files before event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function storeBeforeEventFiles()
	{
		// Get session instance.
		$session = Factory::getSession();

		// Delete stored session value.
		$this->purge();

		// Get list and store in session.
		$list = $this->getOverrideCoreList();
		$session->set('override.beforeEventFiles', $list);
	}

	/**
	 * Method to store files after event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function storeAfterEventFiles()
	{
		// Get session instance
		$session = Factory::getSession();

		// Get list and store in session.
		$list = $this->getOverrideCoreList();
		$session->set('override.afterEventFiles', $list);
	}

	/**
	 * Method to prepare changed or updated core file.
	 *
	 * @param   string  $action  The name of the action.
	 *
	 * @return   array   A list of changed files.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getUpdatedFiles($action)
	{
		// Get session instance
		$session = Factory::getSession();

		$after  = $session->get('override.afterEventFiles');
		$before = $session->get('override.beforeEventFiles');
		$size1  = count($after);
		$size2  = count($before);

		$result = array();

		if ($size1 === $size2)
		{
			for ($i = 0; $i < $size1; $i++)
			{
				if ($after[$i]->coreFile !== $before[$i]->coreFile)
				{
					$after[$i]->action = $action;
					unset($after[$i]->coreFile);
					$result[] = $after[$i];
				}
			}
		}

		return $result;
	}

	/**
	 * Method to get core list of override files.
	 *
	 * @return   array  The list of core files.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOverrideCoreList()
	{
		// Get template model
		$templateModel = $this->getModel();
		$result = $templateModel->getCoreList();

		return $result;
	}

	/**
	 * Last process of this plugin.
	 *
	 * @param   array  $result  Result aray.
	 *
	 * @return   boolean  True/False
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function finalize($result)
	{
		$num = count($result);

		if ($num != 0)
		{
			$span = '<span class="badge badge-light">' . $num . '</span>';
			$this->app->enqueueMessage(Text::sprintf('PLG_INSTALLER_OVERRIDE_FILE_UPDATED', $span), 'notice');
			$this->saveOverrides($result);
		}

		// Delete stored session value.
		$this->purge();
	}

	/**
	 * Event before extension update.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionBeforeUpdate()
	{
		$this->storeBeforeEventFiles();
	}

	/**
	 * Event after extension update.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterUpdate()
	{
		$this->storeAfterEventFiles();
		$result = $this->getUpdatedFiles('Extension Update');
		$this->finalize($result);
	}

	/**
	 * Event before joomla update.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onJoomlaBeforeUpdate()
	{
		$this->storeBeforeEventFiles();
	}

	/**
	 * Event after joomla update.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onJoomlaAfterUpdate()
	{
		$this->storeAfterEventFiles();
		$result = $this->getUpdatedFiles('Joomla Update');
		$this->finalize($result);
	}

	/**
	 * Event before install.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onInstallerBeforeInstaller()
	{
		$this->storeBeforeEventFiles();
	}

	/**
	 * Event after install.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onInstallerAfterInstaller()
	{
		$this->storeAfterEventFiles();
		$result = $this->getUpdatedFiles('Extension Install');
		$this->finalize($result);
	}

	/**
	 * Check for existing id.
	 *
	 * @param   string   $id    Hash id of file.
	 * @param   integer  $exid  Extension id of file.
	 *
	 * @return   boolean  True/False
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function load($id, $exid)
	{
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName('hash_id'))
			->from($db->quoteName('#__template_overrides'))
			->where($db->quoteName('hash_id') . ' = ' . $db->quote($id))
			->where($db->quoteName('extension_id') . ' = ' . $db->quote($exid));

		$db->setQuery($query);
		$results = $db->loadObjectList();

		if (count($results) === 1)
		{
			return true;
		}

		return false;
	}

	/**
	 * Save the updated files.
	 *
	 * @param   array  $pks  Updated files.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function saveOverrides($pks)
	{
		$db = Factory::getDbo();
		$today = Factory::getDate();

		// Insert columns.
		$columns = array(
			'template',
			'hash_id',
			'extension_id',
			'state',
			'action',
			'client_id',
			'created_date',
			'modified_date'
		);

		// Create a insert query.
		$insertQuery = $db->getQuery(true)
			->insert($db->quoteName('#__template_overrides'))
			->columns($db->quoteName($columns));

		foreach ($pks as $pk)
		{
			$insertQuery->clear('values');

			$createdDate = $today->format('y-m-d h:i:s');

			if (empty($pk->modifiedDate))
			{
				$pk->modifiedDate = '0000-00-00 00:00:00';
			}

			if ($this->load($pk->id, $pk->extension_id))
			{
				$updateQuery = $db->getQuery(true)
					->update($db->quoteName('#__template_overrides'))
					->set(
						array($db->quoteName('modified_date') . ' = ' . $db->quote($pk->modifiedDate),
						$db->quoteName('action') . ' = ' . $db->quote($pk->action),
						$db->quoteName('state') . ' = ' . 0)
						)
					->where($db->quoteName('hash_id') . ' = ' . $db->quote($pk->id))
					->where($db->quoteName('extension_id') . ' = ' . $db->quote($pk->extension_id));

					try
					{
						// Set the query using our newly populated query object and execute it.
						$db->setQuery($updateQuery);
						$db->execute();
					}
					catch (\RuntimeException $e)
					{
						return $e;
					}

				continue;
			}

			// Insert values.
			$values = array(
				$db->quote($pk->template),
				$db->quote($pk->id),
				$db->quote($pk->extension_id),
				0,
				$db->quote($pk->action),
				(int) $pk->client,
				$db->quote($createdDate),
				$db->quote($pk->modifiedDate)
			);

			$insertQuery->values(implode(',', $values));

			try
			{
				// Set the query using our newly populated query object and execute it.
				$db->setQuery($insertQuery);
				$db->execute();
			}
			catch (\RuntimeException $e)
			{
				return $e;
			}
		}
	}
}
