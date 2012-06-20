<?php
/**
 * @package    Joomla.Installation
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla Application class
 *
 * Provide many supporting API functions
 *
 * @package  Joomla.Installation
 */
class JInstallation extends JApplication
{
	/**
	* Class constructor
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*                          Recognized key values include 'clientId' (this list is not meant to be comprehensive).
	*/
	public function __construct(array $config = array())
	{
		$config['clientId'] = 2;
		parent::__construct($config);

		$this->_createConfiguration('');

		// Set the root in the URI based on the application name.
		JURI::root(null, str_replace('/' . $this->getName(), '', JURI::base(true)));
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

		switch ($document->getType())
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
		require_once JPATH_COMPONENT . '/controller.php';

		// Execute the task.
		$controller	= JControllerLegacy::getInstance('Installation');
		$controller->execute(JRequest::getVar('task'));
		$controller->redirect();

		// Get output from the buffer and clean it.
		$contents = ob_get_contents();
		ob_end_clean();

		$file = JRequest::getCmd('tmpl', 'index');

		$params = array(
			'template'	=> 'template',
			'file'		=> $file . '.php',
			'directory' => JPATH_THEMES,
			'params'	=> '{}'
		);

		$document->setBuffer($contents, 'installation');
		$document->setTitle(JText::_('INSTL_PAGE_TITLE'));

		$data = $document->render(false, $params);
		JResponse::setBody($data);
		if (JFactory::getConfig()->get('debug_lang'))
		{
			$this->debugLanguage();
		}
	}

	/**
	 * Initialise the application.
	 *
	 * @param   array  $options
	 *
	 * @return  void
	 */
	public function initialise($options = array())
	{
		// Get the localisation information provided in the localise.xml file.
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
			}
			else
			{
				$options['language'] = JLanguageHelper::detectLanguage();
				if (empty($options['language']))
				{
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
		$conf->set('sampledata', $forced['sampledata']);
	}

	/**
	 * @return	void
	 */
	public static function debugLanguage()
	{
		ob_start();
		$lang = JFactory::getLanguage();
		echo '<h4>Parsing errors in language files</h4>';
		$errorfiles = $lang->getErrorFiles();

		if (count($errorfiles))
		{
			echo '<ul>';

			foreach ($errorfiles as $file => $error)
			{
				echo "<li>$error</li>";
			}
			echo '</ul>';
		}
		else
		{
			echo '<pre>None</pre>';
		}

		echo '<h4>Untranslated Strings</h4>';
		echo '<pre>';
		$orphans = $lang->getOrphans();

		if (count($orphans))
		{
			ksort($orphans, SORT_STRING);

			foreach ($orphans as $key => $occurance)
			{
				$guess = str_replace('_', ' ', $key);

				$parts = explode(' ', $guess);
				if (count($parts) > 1)
				{
					array_shift($parts);
					$guess = implode(' ', $parts);
				}

				$guess = trim($guess);

				$key = trim(strtoupper($key));
				$key = preg_replace('#\s+#', '_', $key);
				$key = preg_replace('#\W#', '', $key);

				// Prepare the text
				$guesses[] = $key . '="' . $guess . '"';
			}

			echo "\n\n# " . $file . "\n\n";
			echo implode("\n", $guesses);
		}
		else
		{
			echo 'None';
		}
		echo '</pre>';
		$debug = ob_get_clean();
		JResponse::appendBody($debug);
	}

	/**
	 * Set configuration values
	 *
	 * @param   array   $vars       Array of configuration values
	 * @param   string  $namespace  The namespace
	 *
	 * @return	void
	 */
	public function setCfg(array $vars = array(), $namespace = 'config')
	{
		$this->_registry->loadArray($vars, $namespace);
	}

	/**
	 * Create the configuration registry
	 *
	 * @return	void
	 */
	public function _createConfiguration($file)
	{
		// Create the registry with a default namespace of config which is read only
		$this->_registry = new JRegistry('config');
	}

	/**
	* Get the template
	*
	* @return  string  The template name
	*/
	public function getTemplate($params = false)
	{
		if ((bool) $params)
		{
			$template = new stdClass;
			$template->template = 'template';
			$template->params = new JRegistry;
			return $template;
		}
		return 'template';
	}

	/**
	 * Create the user session
	 *
	 * @param   string  $name  The sessions name
	 *
	 * @return  JSession
	 */
	public function _createSession($name)
	{
		$options = array();
		$options['name'] = $name;

		$session = JFactory::getSession($options);
		if (!$session->get('registry') instanceof JRegistry)
		{
			// Registry has been corrupted somehow
			$session->set('registry', new JRegistry('session'));
		}

		return $session;
	}

	/**
	 * Returns the language code and help url set in the localise.xml file.
	 * Used for forcing a particular language in localised releases.
	 *
	 * @return	bool|array	False on failure, array on success.
	 */
	public function getLocalise()
	{
		$xml = JFactory::getXML(JPATH_SITE . '/installation/localise.xml');

		if (!$xml)
		{
			return false;
		}

		// Check that it's a localise file
		if ($xml->getName() != 'localise')
		{
			return false;
		}

		$ret = array();

		$ret['language'] = (string) $xml->forceLang;
		$ret['helpurl'] = (string) $xml->helpurl;
		$ret['debug'] = (string) $xml->debug;
		$ret['sampledata'] = (string) $xml->sampledata;

		return $ret;
	}

	/**
	 * Returns the installed language files in the administrative and
	 * front-end area.
	 *
	 * @param   boolean  $db
	 *
	 * @return array Array with installed language packs in admin and site area
	 */
	public function getLocaliseAdmin($db = false)
	{
		jimport('joomla.filesystem.folder');

		// Read the files in the admin area
		$path = JLanguage::getLanguagePath(JPATH_ADMINISTRATOR);
		$langfiles['admin'] = JFolder::folders($path);

		// Read the files in the site area
		$path = JLanguage::getLanguagePath(JPATH_SITE);
		$langfiles['site'] = JFolder::folders($path);

		if ($db)
		{
			$langfiles_disk = $langfiles;
			$langfiles = array();
			$langfiles['admin'] = array();
			$langfiles['site'] = array();
			$query = $db->getQuery(true);
			$query->select('element,client_id');
			$query->from('#__extensions');
			$query->where('type = ' . $db->quote('language'));
			$db->setQuery($query);
			$langs = $db->loadObjectList();
			foreach ($langs as $lang)
			{
				switch ($lang->client_id)
				{
					// Site
					case 0:
						if (in_array($lang->element, $langfiles_disk['site']))
						{
							$langfiles['site'][] = $lang->element;
						}
						break;

					// Administrator
					case 1:
						if (in_array($lang->element, $langfiles_disk['admin']))
						{
							$langfiles['admin'][] = $lang->element;
						}
					break;
				}
			}
		}

		return $langfiles;
	}
}
