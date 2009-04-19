<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigControllerApplication extends JController
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('apply', 'save');
	}

	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @return	void
	 */
	public function display()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_config', false));
	}

	/**
	 * Save the configuration
	 */
	public function save()
	{
		JRequest::checkToken() or jExit(JText::_('JInvalid_Token'));

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$app = JFactory::getApplication();
		$model	= $this->getModel('Application');
		$form = $model->getForm();
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		$data	= $form->filter($data);
		$return	= $form->validate($data);

		if (JError::isError($return)) {
			$this->setRedirect(JRoute::_('index.php?option=com_config', false), $return->getMessage(), 'error');
			return false;
		}

		if (empty($data['log_path'])) {
			$data['log_path'] = JPATH_ROOT.DS.'logs';
		}

		if (empty($data['tmp_path'])) {
			$data['tmp_path'] = JPATH_ROOT.DS.'tmp';
		}

		if (!empty($data['live_site'])) {
			$data['live_site'] = rtrim($data['live_site'], '/\\');
		}

		$memcache = array();
		if ($data['cache_handler'] == 'memcache' || $data['session_handler'] == 'memcache') {
			$memcache['persistent'] = $data['memcache_persistent'];
			$memcache['compression'] = $data['memcache_compression'];
			$memcache['servers'][0]['host'] = $data['memcache_host'];
			$memcache['servers'][0]['port'] = $data['memcache_port'];
		}
		unset($data['memcache_persistent']);
		unset($data['memcache_compression']);
		unset($data['memcache_host']);
		unset($data['memcache_port']);

		$config = new JRegistry('config');
		$config->loadArray($data);

		$config->setValue('config.memcache_settings', $memcache);

		// These are the fields that can only be edited manually
		$config->setValue('config.root_user', $app->getCfg('root_user'));
		$config->setValue('config.live_site', $app->getCfg('live_site'));

		//override any possible database password change
		$config->setValue('config.password', $app->getCfg('password'));

		// handling of special characters
		$sitename			= htmlspecialchars($data['sitename'], ENT_COMPAT, 'UTF-8');
		$config->setValue('config.sitename', $sitename);

		$MetaDesc			= htmlspecialchars($data['MetaDesc'],  ENT_COMPAT, 'UTF-8');
		$config->setValue('config.MetaDesc', $MetaDesc);

		$MetaKeys			= htmlspecialchars($data['MetaKeys'],  ENT_COMPAT, 'UTF-8');
		$config->setValue('config.MetaKeys', $MetaKeys);

		// handling of quotes (double and single) and amp characters
		// htmlspecialchars not used to preserve ability to insert other html characters
		$offline_message	= $data['offline_message'];
		$offline_message	= JFilterOutput::ampReplace($offline_message);
		$offline_message	= str_replace('"', '&quot;', $offline_message);
		$offline_message	= str_replace("'", '&#039;', $offline_message);
		$config->setValue('config.offline_message', $offline_message);

		//purge the database session table (only if we are changing to a db session store)
		if($app->getCfg('session_handler') != 'database' && $config->getValue('session_handler') == 'database')
		{
			$table =& JTable::getInstance('session');
			$table->purge(-1);
		}

		// Get the path of the configuration file
		$fname = JPATH_CONFIGURATION.DS.'configuration.php';

		// Update the credentials with the new settings
		$oldconfig =& JFactory::getConfig();
		$oldconfig->setValue('config.ftp_enable', $data['ftp_enable']);
		$oldconfig->setValue('config.ftp_host', $data['ftp_host']);
		$oldconfig->setValue('config.ftp_port', $data['ftp_port']);
		$oldconfig->setValue('config.ftp_user', $data['ftp_user']);
		$oldconfig->setValue('config.ftp_pass', $data['ftp_pass']);
		$oldconfig->setValue('config.ftp_root', $data['ftp_root']);
		JClientHelper::getCredentials('ftp', true);

		if(!$config->get('caching') && $oldconfig->get('caching')) {
			$cache = JFactory::getCache();
			$cache->clean();
		}

		// Try to make configuration.php writeable
		jimport('joomla.filesystem.path');
		if (!$ftp['enabled'] && JPath::isOwner($fname) && !JPath::setPermissions($fname, '0644')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writable');
		}

		// Get the config registry in PHP class format and write it to configuation.php
		jimport('joomla.filesystem.file');
		if (JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'JConfig')))) {
			$msg = JText::_('The Configuration Details have been updated');
		} else {
			$msg = JText::_('ERRORCONFIGFILE');
		}

		// Redirect appropriately
		$task = $this->getTask();
		switch ($task) {
			case 'apply' :
				$this->setRedirect(JRoute::_('index.php?option=com_config', false), $msg);
				break;
			case 'save' :
			default :
				$this->setRedirect(JRoute::_('index.php', false), $msg);
				break;
		}

		// Try to make configuration.php unwriteable
		//if (!$ftp['enabled'] && JPath::isOwner($fname) && !JPath::setPermissions($fname, '0444')) {
		if ($config_array['ftp_enable']==0 && !$ftp['enabled'] && JPath::isOwner($fname) && !JPath::setPermissions($fname, '0444')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php unwritable');
		}
	}

	/**
	 * Cancel operation
	 */
	public function cancel()
	{
		JRequest::checkToken() or jExit(JText::_('JInvalid_Token'));

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$this->setRedirect(JRoute::_('index.php', false));
	}

	public function refreshHelp()
	{
		JRequest::checkToken() or jExit(JText::_('JInvalid_Token'));

		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('http://help.joomla.org/helpsites-15.xml')) === false) {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH ERROR FETCH'), 'error');
		} else if (!JFile::write(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $data)) {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH ERROR STORE'), 'error');
		} else {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH SUCCESS'));
		}
	}
}