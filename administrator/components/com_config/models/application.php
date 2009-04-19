<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modelitem');

/**
 * Weblinks Component Weblink Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @since		1.6
 */
class ConfigModelApplication extends JModel
{
	protected $_config;

	public function getConfig()
	{
		if (empty($this->_config)) {
			$config = new JConfig();

			// MEMCACHE SETTINGS
			if (!empty($config->memcache_settings) && !is_array($config->memcache_settings)) {
				$config->memcache_settings = unserialize(stripslashes($config->memcache_settings));
			}
			if ($config->cache_handler == 'memcache' || $config->session_handler == 'memcache') {
				$this->setState('memcache', true);
			}

			$this->_config = $config;
		}

		return $this->_config;
	}

	/**
	 * Method to get the group form.
	 *
	 * @access	public
	 * @return	mixed	JXForm object on success, false on failure.
	 * @since	1.0
	 */
	public function &getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms');
		JForm::addFieldPath(JPATH_COMPONENT.DS.'models'.DS.'fields');
		$form = &JForm::getInstance('jform', 'config', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		return $form;
	}
}
