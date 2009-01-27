<?php

/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla.Installation
 * @subpackage	Installation
 */

jimport('joomla.application.component.controller');
require_once dirname(__FILE__).DS.'models'.DS.'model.php';
require_once dirname(__FILE__).DS.'views'.DS.'install'.DS.'view.php';

class JInstallationController extends JController
{
	protected $_model		= null;

	protected $_view		= null;


	/**
	 * Constructor
	 */
	function __construct($config = array())
	{
		$config['name']	= 'JInstallation';
		parent::__construct($config);
	}

	/**
	 *
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function dbconfig()
	{
		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (! $model->dbConfig())
		{
			$view->error();
			return false;
		}

		$view->dbConfig();

		return true;
	}

	/**
	 * Overload the parent controller method to add a check for configuration variables
	 *  when a task has been provided
	 *
	 * @param	String $task Task to perform
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function execute($task)
	{
		$appl = JFactory::getApplication();

		// Sanity check
		if ($task && ($task != 'lang') && ($task != 'removedir'))
		{

			/**
			 * To get past this point, a cookietest must be carried in the user's state.
			 * If the state is not set, then cookies are probably disabled.
			 **/

			$goodEnoughForMe = $appl->getUserState('application.cookietest');

			if (! $goodEnoughForMe)
			{
				$model	= &$this->getModel();
				$model->setError(JText::_('WARNCOOKIESNOTENABLED'));
				$view	= &$this->getView();
				$view->error();
				return false;
			}

		}
		else
		{
			// Zilch the application registry - start from scratch
			$session	= &JFactory::getSession();
			$registry	= &$session->get('registry');
			$registry->makeNameSpace('application');

			// Set the cookie test seed
			$appl->setUserState('application.cookietest', 1);
		}

		parent::execute($task);
	}

	/**
	 * Initialize data for the installation
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function initialize()
	{
		return true;
	}

	/**
	 * Present form for FTP information
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function ftpconfig()
	{
		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (! $model->ftpConfig())
		{
			$view->error();
			return false;
		}

		$view->ftpConfig();

		return true;
	}

	/**
	 * Get the model for the installer component
	 *
	 * @return	JInstallerModel
	 * @access	protected
	 * @since	1.5
	 */
	function &getModel()
	{

		if (! $this->_model)
		{
			$this->_model	= new JInstallationModel();
		}

		return $this->_model;
	}

	/**
	 * Get the view for the installer component
	 *
	 * @return	JInstallerView
	 * @access	protected
	 * @since	1.5
	 */
	function &getView()
	{

		if (! $this->_view)
		{
			$this->_view	= new JInstallationView();
			$model	= &$this->getModel();
			$model->test = "blah";
			$this->_view->setModel($model, true);
		}

		return $this->_view;
	}

	/**
	 * Present license information
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function license()
	{
		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (! $model->license())
		{
			$view->error();
			return false;
		}

		$view->license();

		return true;
	}

	/**
	 * Present a choice of languages
	 *
	 * Step One!
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function lang()
	{
		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (! $model->chooseLanguage())
		{
			$view->error();
			return false;
		}

		$view->chooseLanguage();

		return true;
	}

	/**
	 *
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function makedb()
	{
		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (! $model->makeDB())
		{
			$view->error();
			return false;
		}

		if (! $model->ftpConfig(1))
		{
			$view->error();
			return false;
		}

		$view->ftpConfig();

		return true;
	}

	/**
	 * Present the main configuration options
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function mainconfig()
	{

		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (! $model->mainConfig())
		{
			$view->error();
			return false;
		}

		$view->mainConfig();

		return true;
	}

	/**
	 * Present a preinstall check
	 *
	 * Step Two!
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function preinstall()
	{
		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (! $model->preInstall())
		{
			$view->error();
			return true;
		}

		$view->preInstall();

		return true;
	}

	/**
	 * Remove directory messages
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function removedir()
	{
		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (! $model->removedir())
		{
			$view->error();
			return true;
		}

		$view->removedir();

		return true;
	}

	/**
	 *
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function saveconfig()
	{
		$model	= &$this->getModel();
		$view	= &$this->getView();

		if (!$model->saveConfig())
		{
			$view->error();
			return false;
		}

		if (!$model->finish())
		{
			$view->error();
			return false;
		}

		$view->finish();

		return true;
	}

	function migration()
	{
		$model = &$this->getModel();

		$view = &$this->getView();
		if (!$model->checkUpload()) {
			$view->error();
			return false;
		}

		if (!$model->dumpLoad())
		{
			$view->error();
			return false;
		}

		$view->migration();
		return true;
	}

	function postmigrate()
	{
		$model = &$this->getModel();
		$view = &$this->getView();
		if ($model->postMigrate()) {
			// errors!
		}
	}
}
