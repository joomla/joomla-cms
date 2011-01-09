<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla Application class
 *
 * Provide many supporting API functions
 *
 * @package		Joomla.Installation
 */
class JInstallation extends JApplication
{
	/**
	 * The url of the site
	 *
	 * @var string
	 */
	protected $_siteURL = null;

	/**
	* Class constructor
	*
	* @access protected
	* @param	array An optional associative array of configuration settings.
	* Recognized key values include 'clientId' (this list is not meant to be comprehensive).
	*/
	public function __construct(array $config = array())
	{
		$config['clientId'] = 2;
		parent::__construct($config);

		JError::setErrorHandling(E_ALL, 'Ignore');
		$this->_createConfiguration();

		// Set the root in the URI based on the application name.
		JURI::root(null, str_replace('/'.$this->getName(), '', JURI::base(true)));
	}

	/**
	 * Render the application
	 *
	 * @return	void
	 */
	public function render()
	{
		$document = JFactory::getDocument();
		$config = JFactory::getConfig();
		$user = JFactory::getUser();

		switch($document->getType())
		{
			case 'html' :
				// Set metadata
				$document->setTitle(JText::_('INSTL_PAGE_TITLE'));
				break;
			default :
				break;
		}

		// Define component path
		define('JPATH_COMPONENT', JPATH_BASE);
		define('JPATH_COMPONENT_SITE', JPATH_SITE);
		define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR);

		// Start the output buffer.
		ob_start();

		// Import the controller.
		require_once JPATH_COMPONENT.'/controller.php';

		// Execute the task.
		$controller	= JController::getInstance('JInstallation');
		$controller->execute(JRequest::getVar('task'));
		$controller->redirect();

		// Get output from the buffer and clean it.
		$contents = ob_get_contents();
		ob_end_clean();

		$params = array(
			'template'	=> 'template',
			'file'		=> 'index.php',
			'directory' => JPATH_THEMES,
			'params'	=> '{}'
		);

		$document->setBuffer($contents, 'installation');
		$document->setTitle(JText::_('INSTL_PAGE_TITLE'));
		$data = $document->render(false, $params);
		JResponse::setBody($data);
	}

	/**
	 * Initialise the application.
	 *
	 * @param	$options	array
	 */
	public function initialise(array $options = array())
	{
		//Get the localisation information provided in the localise.xml file.
		$forced = $this->getLocalise();

		// Check the request data for the language.
		if (empty($options['language']))
		{
			$requestLang = JRequest::getCmd('lang', null);
			if (!is_null($requestLang))
			{
				$options['language'] = $requestLang;
			}
		}

		// Check the session for the language.
		if (empty($options['language']))
		{
			$sessionLang = JFactory::getSession()->get('setup.language');
			if (!is_null($sessionLang))
			{
				$options['language'] = $sessionLang;
			}
		}

		// This could be a first-time visit - try to determine what the client accepts.
		if (empty($options['language']))
		{
			if (!empty($forced['language']))
			{
				$options['language'] = $forced['language'];
			} else {
				jimport('joomla.language.helper');
				$options['language'] = JLanguageHelper::detectLanguage();
				if (empty($options['language'])) {
					$options['language'] = 'en-GB';
				}
			}
		}

		// Give the user English
		if (empty($options['language']))
		{
			$options['language'] = 'en-GB';
		}

		// Set the language in the class
		$conf = JFactory::getConfig();
		$conf->set('language', $options['language']);
		$conf->set('debug_lang', $forced['debug']);

		// Load Library language 
		// Commented for now as it systematically loads en-GB.lib_joomla.ini 
		// instead of the equivalent strings in the installation ini file when no lang .lib_joomla ini is present
		
		//$lang = JFactory::getLanguage();
		//$lang->load('lib_joomla', JPATH_SITE)
		//|| $lang->load('lib_joomla', JPATH_ADMINISTRATOR);

	}

	/**
	 * Set configuration values
	 *
	 * @param	array	Array of configuration values
	 * @param	string	The namespace
	 */
	public function setCfg(array $vars = array(), $namespace = 'config')
	{
		$this->_registry->loadArray($vars, $namespace);
	}

	/**
	 * Create the configuration registry
	 */
	public function _createConfiguration()
	{
		jimport('joomla.registry.registry');

		// Create the registry with a default namespace of config which is read only
		$this->_registry = new JRegistry('config');
	}

	/**
	* Get the template
	*
	* @return string The template name
	*/
	public function getTemplate($params = false)
	{
		if ((bool) $params)
		{
			$template = new stdClass();
			$template->template = 'template';
			$template->params = new JRegistry;
			return $template;
		}
		return 'template';
	}

	/**
	 * Create the user session
	 *
	 * @param	string	The sessions name
	 * @return	object	JSession
	 */
	public function & _createSession($name)
	{
		$options = array();
		$options['name'] = $name;

		$session = JFactory::getSession($options);
		if (!is_a($session->get('registry'), 'JRegistry'))
		{
			// Registry has been corrupted somehow
			$session->set('registry', new JRegistry('session'));
		}

		return $session;
	}

	/**
	 * Returns the langauge code and help url set in the localise.xml file.
	 * Used for forcing a particular language in localised releases.
	 *
	 * @return	bool|array	False on failure, array on success.
	 */
	public function getLocalise()
	{
		$xml = JFactory::getXML(JPATH_SITE.DS.'installation'.DS.'localise.xml');

		if( ! $xml)
		{
			return false;
		}

		// Check that it's a localise file
		if ($xml->getName() != 'localise')
		{
			return false;
		}

		$ret = array();

		$ret['language'] = (string)$xml->forceLang;
		$ret['helpurl'] = (string)$xml->helpurl;
		$ret['debug'] = (string)$xml->debug;

		return $ret;

	}

	/**
	 * Returns the installed admin language files in the administrative and
	 * front-end area.
	 *
	 * @return	array	Array with installed language packs in admin area
	 */
	public function getLocaliseAdmin()
	{
		jimport('joomla.filesystem.folder');

		// Read the files in the admin area
		$path = JLanguage::getLanguagePath(JPATH_SITE.DS.'administrator');
		$langfiles['admin'] = JFolder::folders($path);

		$path = JLanguage::getLanguagePath(JPATH_SITE);
		$langfiles['site'] = JFolder::folders($path);

		return $langfiles;
	}
}