<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\Container\Container;
use FOF30\Model\DataModel;
use Joomla\CMS\Language\Text;
use RuntimeException;

/**
 * Backup profile model
 *
 * @property  int    id             Profile ID
 * @property  string description    Description
 * @property  string configuration  Engine configuration data
 * @property  string filters        Engine filters
 * @property  int    quickicon      Should I include this profile in the One Click Backup profiles (1) or not (0)?
 */
class Profiles extends DataModel
{
	public function __construct(Container $container, array $config)
	{
		$defaultConfig = [
			'tableName'   => '#__ak_profiles',
			'idFieldName' => 'id',
		];

		if (!is_array($config) || empty($config))
		{
			$config = [];
		}

		$config = array_merge($defaultConfig, $config);

		parent::__construct($container, $config);

		$this->addBehaviour('filters');
		$this->blacklistFilters([
			'configuration',
			'filters',
		]);
	}

	/**
	 * Tries to copy the currently loaded to a new record
	 *
	 * @return  self  The new record
	 */
	public function copy($data = null)
	{
		$id = $this->getId();

		// Check for invalid id's (not numeric, or <= 0)
		if ((!is_numeric($id)) || ($id <= 0))
		{
			throw new DataModel\Exception\RecordNotLoaded('PROFILE_INVALID_ID');
		}

		if (!is_array($data))
		{
			$data = [];
		}

		$data['id'] = 0;

		return $this->getClone()->save($data);
	}

	/**
	 * Returns an associative array with profile IDs as keys and the post-processing engine as values
	 *
	 * @return  array
	 */
	public function getPostProcessingEnginePerProfile()
	{
		// Cache the current profile's ID
		$currentProfileID = $this->container->platform->getSessionVar('profile', null, 'akeeba');

		// Get the IDs of all profiles
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__ak_profiles'));
		$db->setQuery($query);
		$profiles = $db->loadColumn();

		// Initialise return;
		$engines = [];

		// Loop all profiles
		foreach ($profiles as $profileId)
		{
			Platform::getInstance()->load_configuration($profileId);
			$profileConfiguration = Factory::getConfiguration();
			$engines[$profileId]  = $profileConfiguration->get('akeeba.advanced.postproc_engine');
		}

		// Reload the current profile
		Platform::getInstance()->load_configuration($currentProfileID);

		return $engines;
	}

	/**
	 * Runs before deleting a record
	 *
	 * @param   int  $id  The ID of the record being deleted
	 */
	public function onBeforeDelete(&$id)
	{
		// You cannot delete the default record
		if ($id <= 1)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_PROFILE_ERR_CANNOTDELETEDEFAULT'), 500);
		}

		// If you're deleting the current backup profile we have to switch to the default profile (#1)
		$activeProfile = Platform::getInstance()->get_active_profile();

		if ($id == $activeProfile)
		{
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_PROFILE_ERR_CANNOTDELETEACTIVE', $id), 500);
		}
	}

	/**
	 * Save a profile from imported configuration data. The $data array must contain the keys description (profile
	 * description), configuration (engine configuration INI data) and filters (inclusion and inclusion filters JSON
	 * configuration data).
	 *
	 * @param   array  $data  See above
	 *
	 * @returns  void
	 *
	 * @throws   RuntimeException  When an iport error occurs
	 */
	public function import($data)
	{
		// Check for data validity
		$isValid =
			is_array($data) &&
			!empty($data) &&
			array_key_exists('description', $data) &&
			array_key_exists('configuration', $data) &&
			array_key_exists('filters', $data);

		if (!$isValid)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_PROFILES_ERR_IMPORT_INVALID'));
		}

		// Unset the id, if it exists
		if (array_key_exists('id', $data))
		{
			unset($data['id']);
		}

		$data['akeeba.flag.confwiz'] = 1;

		// Try saving the profile
		$result = $this->save($data);

		if (!$result)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_PROFILES_ERR_IMPORT_FAILED'));
		}
	}
}
