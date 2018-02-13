<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Factory;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseDriver;

/**
 * Factory to create MVC factories.
 *
 * @since  __DEPLOY_VERSION__
 */
class MVCFactoryFactory implements MVCFactoryFactoryInterface
{
	/**
	 * The cached db records.
	 *
	 * @var    \stdClass[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $cache;

	/**
	 * The database driver.
	 *
	 * @var    DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	private $db;

	/**
	 * The constructor.
	 *
	 * @param   DatabaseDriver  $db  The database driver.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->db = $db;
	}

	/**
	 * Method to load and return a factory object.
	 *
	 * @param   string                   $extensionName  The name of the extension, eg. com_content.
	 * @param   CMSApplicationInterface  $app            The application.
	 *
	 * @return  \Joomla\CMS\MVC\Factory\MVCFactoryInterface  The factory object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function createFactory($extensionName, CMSApplicationInterface $app)
	{
		if ($this->cache === null)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select($db->quoteName(['extension_id', 'name', 'namespace']))
				->from($db->quoteName('#__extensions'))
				->where('enabled = 1');
			$db->setQuery($query);

			$this->cache = [];
			foreach ($db->loadObjectList() as $extension)
			{
				$this->cache[$extension->name] = $extension;
			}
		}

		if (empty($this->cache[$extensionName]))
		{
			throw new \RuntimeException('Extension '. $extensionName . ' not found to create a MVCFactory for!');
		}

		if ($this->cache[$extensionName]->namespace)
		{
			return new MVCFactory($this->cache[$extensionName]->namespace, $app);
		}

		return new LegacyFactory();
	}
}
