<?php
/**
 * Community Builder Package installer
 * @version $Id: 10/31/13 11:29 PM $
 * @package pkg_communitybuilder
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

defined ( '_JEXEC' ) or die ();

/**
 * Community Builder package installer script.
 */
class pkg_communitybuilderInstallerScript {
	/**
	 * List of supported versions:
	 * Newest version first!
	 * @var array
	 */
	private $versions = array(
		'php' => array (
			'5.6' => '5.6.0',
			'5.5' => '5.5.0',
			'5.4' => '5.4.0',
			'5.3' => '5.3.3',
			'0' => '5.6.8' // Preferred version
		),
		'mysql' => array (
			'5.7' => '5.7',
			'5.6' => '5.6',
			'5.5' => '5.5',
			'5.1' => '5.1',
			'0' => '5.6.24' // Preferred version
		),
		'joomla' => array (
			'3.4' => '3.4.0',
			'3.3' => '3.3.0',
			'3.2' => '3.2.0',
			'3.1' => '3.1.0',
			'3.0' => '3.0.0',
			'2.5' => '2.5.0',
			'0' => '3.4.1' // Preferred version
		)
	);

	/**
	 * List of required PHP extensions.
	 * @var array
	 */
	private $phpExtensions = array ( 'json', 'simplexml' );

	public function install( /** @noinspection PhpUnusedParameterInspection */ $parent ) {
		$session = JFactory::getSession();
		$registry = $session->get('registry');

		if (!is_null($registry))
		{
			echo $registry->get('com_comprofiler_install', null);
			$registry->set('com_comprofiler_install', '');
		}
	}

	public function discover_install( $parent ) {
		$this->install( $parent );
	}

	public function update( $parent ) {
		$this->install( $parent );
	}

	/**
	 * Pre-flight checks
	 * @param  string             $type    Type of install/uninstall
	 * @param  JInstallerPackage  $parent  The parent
	 * @return bool                          true: Can insatll, false: Can't install
	 */
	public function preflight( $type, $parent )
	{
		if ( $type == 'uninstall' )
		{
			return true;
		}

		@set_time_limit( 300 );
		@ini_set( 'memory_limit', '128M' );
		@ini_set( 'post_max_size', '128M' );
		@ini_set( 'upload_max_filesize', '128M' );
		@ini_set( 'error_reporting', 0 );
		@ignore_user_abort( true );

		$installer = $parent->getParent();
		/** @var JInstaller $installer */
		$manifest = $installer->getManifest();

		// Prevent installation if requirements are not met.
		return $this->checkRequirements( $manifest->version );
	}


	public function postflight($type, /** @noinspection PhpUnusedParameterInspection */ $parent) {
		$this->fixUpdateSite();

		// Clear Joomla system cache.
		/** @var JCache|JCacheController $cache */
		$cache = JFactory::getCache();
		$cache->clean('_system');

		// Remove all compiled files from APC cache and from PHP 5.5 OpCache:
		if ( function_exists( 'apc_clear_cache' ) )
		{
			@apc_clear_cache();
		}
		if ( function_exists( 'opcache_reset' ) )
		{
			@opcache_reset();
		}

		if ( $type == 'uninstall' )
		{
			return true;
		}

		// nothing more to do here, rest is done in postflight scripts of plugin and modules.
		// $this->enablePlugin('system', 'communitybuilder');

		return true;
	}

	function enablePlugin($group, $element) {
		$plugin = JTable::getInstance('extension');
		if (!$plugin->load(array('type'=>'plugin', 'folder'=>$group, 'element'=>$element))) {
			return false;
		}
		$plugin->enabled = 1;
		return $plugin->store();
	}

	public function checkRequirements( /** @noinspection PhpUnusedParameterInspection */ $version ) {
		$db		=	JFactory::getDbo();
		$pass	=	$this->checkVersion('php', phpversion())
				&&	$this->checkVersion('joomla', JVERSION)
				&&	$this->checkVersion('mysql', $db->getVersion ())
				&&	$this->checkDbo($db->name, array('mysql', 'mysqli'))
				&&	$this->checkPHPExtensions($this->phpExtensions)
				&&	$this->checkGit();
//				&&	$this->checkCBVersion( $version );
		return $pass;
	}

	// Internal functions

	protected function checkVersion($name, $version)
	{
		$app = JFactory::getApplication();

		$major = 0;
		$minor = 0;

		foreach ($this->versions[$name] as $major=>$minor)
		{
			if (!$major || version_compare($version, $major, '<'))
			{
				continue;
			}
			if (version_compare($version, $minor, '>='))
			{
				return true;
			}
			break;
		}
		if (!$major)
		{
			// Get minimum version, which is the second to last array value:
			end($this->versions[$name]);
			$minor = prev($this->versions[$name]);
		}
		$recommended = end($this->versions[$name]);
		$app->enqueueMessage(sprintf("%s %s is not supported. Minimum required version is %s %s, but it is higly recommended to use %s %s or later.", $name, $version, $name, $minor, $name, $recommended), 'notice');
		return false;
	}

	protected function checkDbo($name, $types) {
		$app = JFactory::getApplication();

		if (in_array($name, $types)) {
			return true;
		}
		$app->enqueueMessage(sprintf("Database driver '%s' is not supported. Please use MySQL instead.", $name), 'notice');
		return false;
	}

	protected function checkPHPExtensions($extensions)
	{
		$app = JFactory::getApplication();

		$pass = true;
		foreach ($extensions as $name)
		{
			if (!extension_loaded($name))
			{
				$pass = false;
				$app->enqueueMessage(sprintf("Required PHP extension '%s' is missing. Please install it into your system.", $name), 'notice');
			}
		}
		return $pass;
	}

	protected function checkGit()
	{
		$app = JFactory::getApplication();

		if ( realpath( JPATH_ADMINISTRATOR . '/components/com_comprofiler/../../../' ) == realpath( JPATH_ADMINISTRATOR ) ) {
			// Do not check for Joomla being git-versioned (only for CB and only if it is in a different, aliased folder):
			return true;
		}

		$gitCb		=	JPATH_ADMINISTRATOR . '/components/com_comprofiler/../../../.git';
		$gitJoomla	=	JPATH_ADMINISTRATOR . '/../.git';
		if ( file_exists ( $gitCb ) && ! file_exists( $gitJoomla ) )
		{
			$app->enqueueMessage('Oops! You tried to install Community Builder over your Git repository! Fortunately we checked and did not allow this', 'error');
			return false;
		}

		return true;
	}

	protected function checkCBVersion( $version )
	{
		if ( defined( 'CBLIB' ) && version_compare( $version, CBLIB, '<' ) )
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage('You are trying to install Community Builder over a newer version!', 'error');
			return false;
		}
		return true;
	}

	protected function fixUpdateSite()
	{
		$db = JFactory::getDbo();

		// Get list of ids of all update sites:
		$query = $db->getQuery(true)
			->select($db->quoteName('update_site_id'))->from($db->quoteName('#__update_sites'))
			->where($db->quoteName('location') . ' LIKE '. $db->quote('http://update.joomlapolis.___/%'))
			->where($db->quoteName('type') . ' <> '. $db->quote('collection'))
			->order($db->quoteName('update_site_id') . ' ASC');
		$db->setQuery($query);
		$list = (array) $db->loadColumn();

		if ($list)
		{
			$ids = implode(',', $list);

			// Remove old update sites (not collection):
			$query = $db->getQuery(true)->delete($db->quoteName('#__update_sites'))->where($db->quoteName('update_site_id') . 'IN ('.$ids.')');
			$db->setQuery($query);
			$db->execute();

			// Remove old updates.
			$query = $db->getQuery(true)->delete($db->quoteName('#__updates'))->where($db->quoteName('update_site_id') . 'IN ('.$ids.')');
			$db->setQuery($query);
			$db->execute();

			// Remove old update extension bindings.
			$query = $db->getQuery(true)->delete($db->quoteName('#__update_sites_extensions'))->where($db->quoteName('update_site_id') . 'IN ('.$ids.')');
			$db->setQuery($query);
			$db->execute();
		}
	}
}
