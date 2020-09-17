<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use AkeebaUsagestats;
use FOF30\Encrypt\Randval;
use FOF30\Model\Model;
use Joomla\CMS\Uri\Uri;

/**
 * Usage statistics collection model. Implements the anonymous collection of PHP, MySQL and Joomla! version information
 * which help us decide on the end of support for obsolete versions of said third party software.
 */
class UsageStatistics extends Model
{
	/**
	 * Get an existing unique site ID or create a new one
	 *
	 * @return  string
	 */
	public function getSiteId()
	{
		// Can I load a site ID from the database?
		$siteId = $this->getCommonVariable('stats_siteid', null);

		// Can I load the site Url from the database?
		$siteUrl = $this->getCommonVariable('stats_siteurl', null);

		// No id or the saved URL is not the same as the current one (ie site restored to a new url)?
		// Create a new, random site ID and save it to the database
		if (empty($siteId) || (md5(Uri::base()) != $siteUrl))
		{
			$siteUrl = md5(Uri::base());
			$this->setCommonVariable('stats_siteurl', $siteUrl);

			$randomData = (new Randval())->genRandomBytes(120);
			$siteId     = sha1($randomData);

			$this->setCommonVariable('stats_siteid', $siteId);
		}

		return $siteId;
	}

	/**
	 * Send site information to the remove collection service
	 *
	 * @param   bool  $useIframe  Should I use an IFRAME?
	 *
	 * @return  bool
	 */
	public function collectStatistics($useIframe)
	{
		// Is data collection turned off?
		if (!$this->container->params->get('stats_enabled', 1))
		{
			return false;
		}

		// Make sure there is a site ID set
		$siteId    = $this->getSiteId();
		$container = $this->container;

		// UsageStats file is missing, no need to continue
		if (!file_exists($container->backEndPath . '/Master/Stats/usagestats.php'))
		{
			return false;
		}

		if (!class_exists('AkeebaUsagestats', false))
		{
			@include_once $container->backEndPath . '/Master/Stats/usagestats.php';
		}

		// UsageStats file is missing, no need to continue
		if (!class_exists('AkeebaUsagestats', false))
		{
			return false;
		}

		$lastrun = $this->getCommonVariable('stats_lastrun', 0);

		// It's not time to collect the stats
		if (time() < ($lastrun + 3600 * 24))
		{
			return false;
		}

		if (!defined('AKEEBA_VERSION'))
		{
			@include_once $container->backEndPath . '/version.php';
		}

		if (!defined('AKEEBA_VERSION'))
		{
			define('AKEEBA_VERSION', 'dev');
			define('AKEEBA_DATE', date('Y-m-d'));
		}

		$db = $container->db;

		try
		{
			$stats = new AkeebaUsagestats();
		}
		catch (\Exception $e)
		{
			return false;
		}

		$stats->setSiteId($siteId);

		// I can't use list since dev release don't have any dots
		$at_parts    = explode('.', AKEEBA_VERSION);
		$at_major    = $at_parts[0];
		$at_minor    = isset($at_parts[1]) ? $at_parts[1] : '';
		$at_revision = isset($at_parts[2]) ? $at_parts[2] : '';

		list($php_major, $php_minor, $php_revision) = explode('.', phpversion());
		$php_qualifier = strpos($php_revision, '~') !== false ? substr($php_revision, strpos($php_revision, '~')) : '';

		list($cms_major, $cms_minor, $cms_revision) = explode('.', JVERSION);
		list($db_major, $db_minor, $db_revision) = explode('.', $db->getVersion());
		$db_qualifier = strpos($db_revision, '~') !== false ? substr($db_revision, strpos($db_revision, '~')) : '';

		$db_driver = get_class($db);

		if (stripos($db_driver, 'mysql') !== false)
		{
			$stats->setValue('dt', 1);
		}
		else
		{
			$stats->setValue('dt', 0);
		}

		$stats->setValue('sw', AKEEBA_PRO ? 2 : 1); // software
		$stats->setValue('pro', AKEEBA_PRO); // pro
		$stats->setValue('sm', $at_major); // software_major
		$stats->setValue('sn', $at_minor); // software_minor
		$stats->setValue('sr', $at_revision); // software_revision
		$stats->setValue('pm', $php_major); // php_major
		$stats->setValue('pn', $php_minor); // php_minor
		$stats->setValue('pr', $php_revision); // php_revision
		$stats->setValue('pq', $php_qualifier); // php_qualifiers
		$stats->setValue('dm', $db_major); // db_major
		$stats->setValue('dn', $db_minor); // db_minor
		$stats->setValue('dr', $db_revision); // db_revision
		$stats->setValue('dq', $db_qualifier); // db_qualifiers
		$stats->setValue('ct', 1); // cms_type
		$stats->setValue('cm', $cms_major); // cms_major
		$stats->setValue('cn', $cms_minor); // cms_minor
		$stats->setValue('cr', $cms_revision); // cms_revision

		// Store the last execution time. We must store it even if we fail since we don't want a failed stats collection
		// to cause the site to stop responding.
		$this->setCommonVariable('stats_lastrun', time());

		$return = $stats->sendInfo($useIframe);

		return $return;
	}

	/**
	 * Load a variable from the common variables table. If it doesn't exist it returns $default
	 *
	 * @param   string  $key      The key to load
	 * @param   mixed   $default  The default value if the key doesn't exist
	 *
	 * @return  mixed  The contents of the key or null if it's not present
	 */
	public function getCommonVariable($key, $default = null)
	{
		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select($db->qn('value'))
			->from($db->qn('#__akeeba_common'))
			->where($db->qn('key') . ' = ' . $db->q($key));

		try
		{
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		catch (\Exception $e)
		{
			$result = $default;
		}

		return $result;
	}

	/**
	 * Set a variable to the common variables table.
	 *
	 * @param   string  $key    The key to save
	 * @param   mixed   $value  The value to save
	 *
	 * @return  void
	 */
	public function setCommonVariable($key, $value)
	{
		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__akeeba_common'))
			->where($db->qn('key') . ' = ' . $db->q($key));

		try
		{
			$db->setQuery($query);
			$count = $db->loadResult();
		}
		catch (\Exception $e)
		{
			return;
		}

		try
		{
			if (!$count)
			{
				$insertObject = (object) [
					'key'   => $key,
					'value' => $value,
				];
				$db->insertObject('#__akeeba_common', $insertObject);
			}
			else
			{
				$keyName = version_compare(JVERSION, '1.7.0', 'lt') ? $db->qn('key') : 'key';

				$insertObject = (object) [
					$keyName => $key,
					'value'  => $value,
				];

				$db->updateObject('#__akeeba_common', $insertObject, $keyName);
			}
		}
		catch (\Exception $e)
		{
		}
	}
}
