<?php
/**
 * @package		 Joomla.Administrator
 * @subpackage	com_installer
 *
 * @copyright	 Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license		 GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Extension Manager Installer Model
 *
 * @since	1.5
 */
class InstallerModelInstaller extends JModelLegacy
{

	/**
	 * [initialize description]
	 * @return [type] [description]
	 */
	public function initialize( $params ){

		// Stage Model
			$this->setState( 'package', $params['package'] );
			$this->setState( 'params', new JRegistry($params['params']) );

		// Identify standalog staging location
			$root_path = JPATH_ROOT;
			$tmp_path	= JFactory::getConfig()->get('tmp_path');
			if (strpos($tmp_path, $root_path) === 0)
			{
				$this->setState('installer_path', $tmp_path . '/com_installer/');
				$this->setState('installer_site', JUri::root(true) . substr($tmp_path,strlen($root_path)) . '/com_installer/');
				$this->setState('installer_file', 'installer.php');
			}
			else
			{
				$this->setState('installer_path', JPATH_ROOT . '/media/com_installer/standalone/');
				$this->setState('installer_site', JUri::root(true) . '/media/com_installer/standalone/');
				$this->setState('installer_file', 'installer.php');
			}

		// Reset Installer
			if (!$this->reset())
			{
				return false;
			}

		// Create Installer
			if (!$this->buildInstaller())
			{
				return false;
			}

		// Prepare Session
			$this->storeSessionState();

		// Success
			return true;

	}

	/**
	 * [finalize description]
	 * @param	[type] $params [description]
	 * @return [type]				 [description]
	 */
	public function finalize( $params ){

		// Stage Model
			$this->stageSessionState();
			$this->setState( 'success', $params['success'] );
			$this->setState( 'message', $params['message'] );

		// Stage
			$package = $this->getState('package');

		// This event allows a custom post-flight:
			JEventDispatcher::getInstance()
				->trigger('onInstallerAfterInstaller', array($this, $package, null, $this->getState('success'), $this->getState('message')));

		// Cleanup the package files
			if (isset($package['packagefile']))
			{
				if (!is_file($package['packagefile']))
				{
					$config = JFactory::getConfig();
					$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
				}
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}

		// Finalize Model
			$this->reset();

	}

	/**
	 * [reset description]
	 * @return [type] [description]
	 */
	public function reset(){

		// Allow provider to reset
			$provider = $this->getInstallerProvider();
			if( $provider ){
				$provider->reset();
			}

		// Delete Previous Files
			$installer_path = $this->getState('installer_path');
			$installer_file = $this->getState('installer_file');
			if (is_readable($installer_path . $installer_file))
			{
				@unlink($installer_path . $installer_file);
			}
			if (is_dir($installer_path))
			{
				@rmdir($installer_path);
			}

		// Reset Session
			JFactory::getApplication()->setUserState('com_installer.installer', array(
				'callurl'	 => $this->getState('installer_site') . $installer_file,
				'returnurl' => 'index.php?option=com_installer&task=installer.finalize'
				));

		// Complete
			return true;

	}

	/**
	 * [storeSessionState description]
	 * @return [type] [description]
	 */
	public function storeSessionState(){

		// Stage
			$app = JFactory::getApplication();
			$state = $this->getState();

		// Push to Session
			foreach ($state AS $key => $val)
			{
				if (strpos($key, '_') !== 0)
				{
					$app->setUserState( 'com_installer.installer.' . $key, $state->{$key} );
				}
			}

	}

	/**
	 * [stageSessionState description]
	 * @return [type] [description]
	 */
	public function stageSessionState(){

		// Stage
			$installer = JFactory::getApplication()->getUserState('com_installer.installer');

		// Pull from Session
			if ($installer)
			{
				foreach ($installer AS $key => $val)
				{
					$this->setState( $key, (is_array($installer) ? $installer[$key] : $installer->{$key}) );
				}
			}

	}

	/**
	 * [getInstallerProvider description]
	 * @return [type] [description]
	 */
	public function getInstallerProvider(){

		// Stage
			$package			 = $this->getState('package');
			$providerClass = null;

		// Load by Type
			require_once JPATH_ADMINISTRATOR . '/components/com_installer/provider/standaloneProvider.php';
			switch ($package['type'])
			{

				case 'file':
					require_once JPATH_ADMINISTRATOR . '/components/com_installer/provider/akeeba.php';
					$providerClass = 'JInstallerStandaloneProviderAkeeba';
					break;

				case 'module':
				case 'plugin':
				case 'component':
					require_once JPATH_ADMINISTRATOR . '/components/com_installer/provider/joomla.php';
					$providerClass = 'JInstallerStandaloneProviderJoomla';
					break;

			}

		// Init Provider
			if ($providerClass && class_exists($providerClass))
			{
				return new $providerClass( array(
					'installer_path' => $this->getState('installer_path'),
					'installer_site' => $this->getState('installer_site'),
					'installer_file' => $this->getState('installer_file'),
					'package' => $this->getState('package'),
					'params' => $this->getState('params')
					));
			}

		// No Provider
			return null;

	}

	/**
	 * [buildInstaller description]
	 * @return [type] [description]
	 */
	public function buildInstaller(){

		// Stage
			$app = JFactory::getApplication();
			$installer_path = $this->getState('installer_path');
			$installer_file = $this->getState('installer_file');

		// Copy Installer to Media
			$is_dir = (is_dir($installer_path) || @mkdir($installer_path, 0755, true));
			$is_writeable = is_writeable($installer_path);
			if ($is_dir && $is_writeable)
			{

				// Remove Existing Installer
					if (is_readable($installer_path . $installer_file))
					{
						@unlink($installer_path . $installer_file);
					}
					if (is_readable($installer_path . $installer_file))
					{
						$app->enqueueMessage(JText::_('COM_INSTALLER_STANDALONE_NOT_WRITABLE'), 'error');
						return false;
					}

				// Trigger Installer Build
					$provider = $this->getInstallerProvider();
					if (!$provider->buildInstaller())
					{
						$app->enqueueMessage(JText::_('COM_INSTALLER_STANDALONE_BUILD_FAILED'), 'error');
						return false;
					}

			}
			else
			{
				$app->enqueueMessage(JText::_('COM_INSTALLER_STANDALONE_NOT_WRITABLE'), 'error');
				return false;
			}

		// Complete
			return true;

	}

}
