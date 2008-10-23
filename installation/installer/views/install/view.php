<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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

jimport('joomla.application.component.view');

class JInstallationView extends JView
{
	/**
	 * The installation steps
	 *
	 * @var		array
	 * @access	protected
	 * @since	1.5
	 */
	protected $_steps		= null;
	protected $subtemplate;
	protected $languages;
	protected $direction;
	protected $version;
	protected $options;
	protected $settings;
	protected $encodings;
	protected $maxupload;
	protected $buffer;

	function __construct($config = array())
	{
		$this->_name	= 'install';

		return parent::__construct($config);
	}

	/**
	 * Language page
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function chooseLanguage()
	{
		$this->_setCurrentStep('lang');

		$model	=& $this->getModel();
		$lists	=& $model->getData('lists');

		$this->assignRef('languages', $lists['langs']);

		return $this->display();
	}

	/**
	 * Set the current step in the display workflow
	 *
	 * @param	string $step
	 * @access	protected
	 * @since	1.6
	 */
	function _setCurrentStep($step)
	{
		$this->assign('subtemplate', $step);

		$steps	= & $this->getSteps();
		$steps[$step] = 'on';
	}

	/**
	 * The DB Config page
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function dbConfig()
	{
		$this->_setCurrentStep('dbconfig');

		$model	=& $this->getModel();
		$lists	=& $model->getData('lists');

		$this->assignRef('options', $lists['dbTypes']);

		return $this->display();
	}


	/**
	 * Display the template
	 *
	 * @param	String $tpl Template
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function display($tpl = null)
	{
		$lang	= JFactory::getLanguage();

		$this->assign('direction', $lang->isRTL() ? 'rtl' : 'ltr');

		return parent::display($tpl);
	}

	/**
	 * Report an error to the user
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function error()
	{
		$this->_setCurrentStep('error');

		$model	= $this->getModel();
		$this->assign('message', $model->getError());
		$this->assign('back', $model->getData('back'));
		$this->assign('errors', $model->getData('errors'));

		return $this->display();
	}

	/**
	 * The the final page
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function finish()
	{
		$this->_setCurrentStep('finish');

		$model	=& $this->getModel();
		$this->assign('buffer', $model->getData('buffer'));

		return $this->display();
	}

	/**
	 * Show the FTP config page
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function ftpConfig()
	{
		$this->_setCurrentStep('ftpconfig');
		return $this->display();
	}

	/**
	 * Get the installation steps
	 *
	 * @return	array
	 * @access	protected
	 * @since	1.5
	 */
	function & getSteps()
	{
		if (is_null($this->_steps))
		{
			$this->_steps = array(
				'lang' => 'off',
				'preinstall' => 'off',
				'license' => 'off',
				'dbconfig' => 'off',
				'ftpconfig' => 'off',
				'mainconfig' => 'off',
				'finish' => 'off'
			);
		}

		return $this->_steps;
	}

	/**
	 * Get a session variable
	 *
	 * @param	string $name Name of the variable
	 * @param	string $default The default value
	 * @return	string
	 * @access	public
	 * @since	1.6
	 */
	function getSessionVar($name, $default = null)
	{
		static $vars;

		if (!$vars) {
			$model	= $this->getModel();
			$vars	=& $model->getVars();
		}

		if (isset($vars[$name])) {
			return $vars[$name];
		}

		return $default;
	}

	/**
	 * The license page
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function license()
	{
		$this->_setCurrentStep('license');

		return $this->display();
	}

	/**
	 * The main configuration page
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function mainConfig()
	{
		$this->_setCurrentStep('mainconfig');

		/*
		 * prepare migration encoding selection
		 */
		$encodings = array('iso-8859-1','iso-8859-2','iso-8859-3','iso-8859-4','iso-8859-5','iso-8859-6','iso-8859-7','iso-8859-8','iso-8859-9','iso-8859-10','iso-8859-13','iso-8859-14','iso-8859-15','cp874','windows-1250','windows-1251','windows-1252','windows-1253','windows-1254','windows-1255','windows-1256','windows-1257','windows-1258','utf-8','big5','euc-jp','euc-kr','euc-tw','iso-2022-cn','iso-2022-jp-2','iso-2022-jp','iso-2022-kr','iso-10646-ucs-2','iso-10646-ucs-4','koi8-r','koi8-ru','ucs2-internal','ucs4-internal','unicode-1-1-utf-7','us-ascii','utf-16');
		$this->assign('encodings', $encodings);

		$max_upload_size = min(JInstallationHelper::let_to_num(ini_get('post_max_size')), JInstallationHelper::let_to_num(ini_get('upload_max_filesize')));
		$this->assign('maxupload', JText::sprintf('UPLOADFILESIZE',(number_format($max_upload_size/(1024*1024), 2))."MB."));

		return $this->display();
	}

	/**
	 * The the pre-install info page
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function preInstall()
	{
		$this->_setCurrentStep('preinstall');

		$model	= $this->getModel();
		$lists	=& $model->getData('lists');

		$version	= new JVersion();

		$this->assign('version', 	$version->getLongVersion());

		$this->assignRef('options', 	$lists['phpOptions']);
		$this->assignRef('settings', $lists['phpSettings']);

		return $this->display();
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
		$this->setLayout('removedir');
		return $this->display();
	}


	function migration()
	{
		$this->_setCurrentStep('migration');
		return $this->display();
	}
}
