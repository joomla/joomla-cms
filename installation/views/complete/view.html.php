<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Install Complete View
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationViewComplete extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 */
	public function display($tpl = null)
	{
		$state = $this->get('State');
		$options = $this->get('Options');

		// Get the config string from the session.
		$session = JFactory::getSession();
		$config = $session->get('setup.config', null);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->state   = $state;
		$this->options = $options;
		$this->config  = $config;

		parent::display($tpl);
	}
	
	public function getDataInstall () {
            require_once JPATH_INSTALLATION.'/helpers/database.php';
            $site = NULL;
            $result = NULL;
            
            $path = str_replace('installation', '', JPATH_BASE);
            
            if(file_exists($path.'configuration.php')){
                require $path.'configuration.php';
                $cfg = new JConfig();
                $site = $cfg->sitename;
                $db = JInstallationHelperDatabase::getDBO($cfg->dbtype, $cfg->host, $cfg->user, $cfg->password, $cfg->db, $cfg->dbprefix);
                $qry = "SELECT * FROM #__users";
                $db->setQuery($qry);
                $result = $db->loadAssocList();
                $result[0]['site'] = $site;
            }
            return (isset($result[0])) ? $result[0] : false;
        }
}
