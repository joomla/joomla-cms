<?php

/**
 * @version		$Id: classes.php 6774 2007-03-05 02:42:09Z friesengeist $
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla
 * @subpackage	Installation
 */

jimport('joomla.application.component.controller');
require_once( dirname(__FILE__).DS.'models'.DS.'model.php');
require_once( dirname(__FILE__).DS.'views'.DS.'install'.DS.'view.php');

class JInstallationController extends JController
{
	var $_model		= null;
	
	var $_view		= null;
	
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		$config['name']	= 'JInstallation';
		
		parent::__construct( $config );
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
		$model	=& $this->getModel();
		$view	=& $this->getView();
		
		$result = $model->dbConfig();
		
		if ( ! $result )
		{
			return $view->error();
			
		}
		
		$view->dbConfig();
		
		return $result;
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
		global $mainframe;
		
		// Sanity check	
		if ( $task && ( $task != 'lang' ) && ( $task != 'preinstall' ) )
		{

			/**
			 * To get past this point, a language must be carried in the user's state.
			 * If the state is not set, then cookies are probably disabled.
			 **/

			$confLang = $mainframe->getUserState('application.lang');

			if ( ! $confLang && false )
			{
				$model	=& $this->getModel();
				$model->setError(JText::_('WARNCOOKIESNOTENABLED'));
				$view	=& $this->getView();
				$view->error();
				return false;
			}

		}
		else
		{
			// Zilch the application registry - start from scratch
			$session	=& JFactory::getSession();
			$registry	=& $session->get('registry');
			$registry->makeNameSpace('application');
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
		$model	=& $this->getModel();
		$view	=& $this->getView();
		
		$result =  $model->ftpConfig();
		
		if ( ! $result )
		{
			return $view->error();
				
		}
		
		$view->ftpConfig();
		
		return $result;
	}

	/**
	 * Get the model for the installer component
	 *
	 * @return	JInstallerModel
	 * @access	protected
	 * @since	1.5
	 */
	function & getModel()
	{

		if ( ! $this->_model )
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
	function & getView()
	{
		
		if ( ! $this->_view )
		{
			$this->_view	= new JInstallationView();
			$model	=& $this->getModel();
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
		$model	=& $this->getModel();
		$view	=& $this->getView();
		
		$result = $model->license();
		
		if ( ! $result )
		{
			return $view->error();
				
		}

		$view->license();
		
		return $result;
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
		$model	=& $this->getModel();
		$view	=& $this->getView();
		
		$result = $model->chooseLanguage();
		
		if ( ! $result )
		{
			return $view->error();
				
		}
		
		$view->chooseLanguage();
		
		return $result;
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
		$model	=& $this->getModel();
		$view	=& $this->getView();
		
		$result = $model->makeDB();
		
		if ( ! $result )
		{
			return $view->error();		
		}

		$result = $model->ftpConfig( 1 );
		
		if ( ! $result )
		{
			return $view->error();
		}
		
		$view->ftpConfig();
		
		return $result;
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
		$model	=& $this->getModel();
		$view	=& $this->getView();
		
		$result =  $model->mainConfig();
		
		if ( ! $result )
		{
			return $view->error();
		}

		$view->mainConfig();
		
		return $result;
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
		$model	=& $this->getModel();
		$view	=& $this->getView();
		
		$result =  $model->preInstall();
		
		if ( ! $result )
		{
			return $view->error();
		}
		
		$view->preInstall();
		
		return $result;
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
		$model	=& $this->getModel();
		$view	=& $this->getView();
		
		$result = $model->saveConfig();
		
		if ( ! $result )
		{
			return $view->error();
		}
		
		$result = $model->finish();
		
		if ( ! $result )
		{
			return $view->error();
		}
		
		$view->finish();
		
		return $result;
	}

}
?>