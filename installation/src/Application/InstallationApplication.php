<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Application;

defined('_JEXEC') or die;

use Joomla\Application\Web\WebClient;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Document\FactoryInterface;
use Joomla\CMS\Exception\ExceptionHandler;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Joomla\Session\SessionEvent;

/**
 * Joomla! Installation Application class.
 *
 * @since  3.1
 */
final class InstallationApplication extends CMSApplication
{
	/**
	 * Class constructor.
	 *
	 * @param   Input      $input      An optional argument to provide dependency injection for the application's input
	 *                                 object.  If the argument is a JInput object that object will become the
	 *                                 application's input object, otherwise a default input object is created.
	 * @param   Registry   $config     An optional argument to provide dependency injection for the application's
	 *                                 config object.  If the argument is a Registry object that object will become
	 *                                 the application's config object, otherwise a default config object is created.
	 * @param   WebClient  $client     An optional argument to provide dependency injection for the application's
	 *                                 client object.  If the argument is a WebClient object that object will become the
	 *                                 application's client object, otherwise a default client object is created.
	 * @param   Container  $container  Dependency injection container.
	 *
	 * @since   3.1
	 */
	public function __construct(Input $input = null, Registry $config = null, WebClient $client = null, Container $container = null)
	{
		// Register the application name.
		$this->name = 'installation';

		// Register the client ID.
		$this->clientId = 2;

		// Run the parent constructor.
		parent::__construct($input, $config, $client, $container);

		// Store the debug value to config based on the JDEBUG flag.
		$this->config->set('debug', JDEBUG);

		// Register the config to Factory.
		Factory::$config = $this->config;

		// Set the root in the URI one level up.
		$parts = explode('/', Uri::base(true));
		array_pop($parts);
		Uri::root(null, implode('/', $parts));
	}

	/**
	 * After the session has been started we need to populate it with some default values.
	 *
	 * @param   SessionEvent  $event  Session event being triggered
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function afterSessionStart(SessionEvent $event)
	{
		$session = $event->getSession();

		if ($session->isNew())
		{
			$session->set('registry', new Registry('session'));
		}
	}

	/**
	 * Method to display errors in language parsing.
	 *
	 * @return  string  Language debug output.
	 *
	 * @since   3.1
	 */
	public function debugLanguage()
	{
		if ($this->getDocument()->getType() != 'html')
		{
			return '';
		}

		$lang   = Factory::getLanguage();
		$output = '<h4>' . \JText::_('JDEBUG_LANGUAGE_FILES_IN_ERROR') . '</h4>';

		$errorfiles = $lang->getErrorFiles();

		if (count($errorfiles))
		{
			$output .= '<ul>';

			foreach ($errorfiles as $error)
			{
				$output .= "<li>$error</li>";
			}

			$output .= '</ul>';
		}
		else
		{
			$output .= '<pre>' . \JText::_('JNONE') . '</pre>';
		}

		$output .= '<h4>' . \JText::_('JDEBUG_LANGUAGE_UNTRANSLATED_STRING') . '</h4>';
		$output .= '<pre>';
		$orphans = $lang->getOrphans();

		if (count($orphans))
		{
			ksort($orphans, SORT_STRING);

			$guesses = array();

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

				$key = strtoupper(trim($key));
				$key = preg_replace('#\s+#', '_', $key);
				$key = preg_replace('#\W#', '', $key);

				// Prepare the text.
				$guesses[] = $key . '="' . $guess . '"';
			}

			$output .= implode("\n", $guesses);
		}
		else
		{
			$output .= '<pre>' . \JText::_('JNONE') . '</pre>';
		}

		$output .= '</pre>';

		return $output;
	}

	/**
	 * Dispatch the application.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function dispatch()
	{
		// Load the document to the API.
		$this->loadDocument();

		// Set up the params
		$document = $this->getDocument();

		// Register the document object with Factory.
		Factory::$document = $document;

		// Define component path.
		define('JPATH_COMPONENT', JPATH_BASE);
		define('JPATH_COMPONENT_SITE', JPATH_SITE);
		define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR);

		// Execute the task.
		ob_start();
		$this->executeController();
		$contents = ob_get_clean();

		// If debug language is set, append its output to the contents.
		if ($this->config->get('debug_lang'))
		{
			$contents .= $this->debugLanguage();
		}

		// Set the content on the document
		$this->getDocument()->setBuffer($contents, 'component');

		// Set the document title
		$document->setTitle(\JText::_('INSTL_PAGE_TITLE'));
	}

	/**
	 * Method to run the Web application routines.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function doExecute()
	{
		// Initialise the application.
		$this->initialiseApp();

		// Dispatch the application.
		$this->dispatch();
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function execute()
	{
		try
		{
			// Perform application routines.
			$this->doExecute();

			// If we have an application document object, render it.
			if ($this->document instanceof Document)
			{
				// Render the application output.
				$this->render();
			}

			// If gzip compression is enabled in configuration and the server is compliant, compress the output.
			if ($this->get('gzip') && !ini_get('zlib.output_compression') && (ini_get('output_handler') != 'ob_gzhandler'))
			{
				$this->compress();
			}
		}
		catch (\Throwable $throwable)
		{
			ExceptionHandler::render($throwable);
		}

		// Send the application response.
		$this->respond();
	}

	/**
	 * Method to load a PHP configuration class file based on convention and return the instantiated data object.  You
	 * will extend this method in child classes to provide configuration data from whatever data source is relevant
	 * for your specific application.
	 *
	 * @param   string  $file   The path and filename of the configuration file. If not provided, configuration.php
	 *                          in JPATH_BASE will be used.
	 * @param   string  $class  The class name to instantiate.
	 *
	 * @return  mixed   Either an array or object to be loaded into the configuration object.
	 *
	 * @since   11.3
	 * @throws  \RuntimeException
	 */
	protected function fetchConfigurationData($file = '', $class = 'JConfig')
	{
		return array();
	}

	/**
	 * Executed a controller from the input task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function executeController()
	{
		$task = $this->input->get('task');

		// The name of the controller
		$controllerName = 'display';

		// Parse task in format controller.task
		if ($task)
		{
			list($controllerName, $task) = explode('.', $task, 2);
		}

		// Compile the class name
		$class = 'Joomla\\CMS\\Installation\\Controller\\' . ucfirst($controllerName) . 'Controller';

		// Create the instance
		$controller = new $class([], new MVCFactory('Joomla\\CMS', $this), $this, $this->input);

		// Execute the task
		$controller->execute($task);
	}

	/**
	 * Returns the language code and help URL set in the localise.xml file.
	 * Used for forcing a particular language in localised releases.
	 *
	 * @return  mixed  False on failure, array on success.
	 *
	 * @since   3.1
	 */
	public function getLocalise()
	{
		$xml = simplexml_load_file(JPATH_INSTALLATION . '/localise.xml');

		if (!$xml)
		{
			return false;
		}

		// Check that it's a localise file.
		if ($xml->getName() !== 'localise')
		{
			return false;
		}

		$ret = array();

		$ret['language']   = (string) $xml->forceLang;
		$ret['helpurl']    = (string) $xml->helpurl;
		$ret['debug']      = (string) $xml->debug;
		$ret['sampledata'] = (string) $xml->sampledata;

		return $ret;
	}

	/**
	 * Returns the installed language files in the administrative and frontend area.
	 *
	 * @param   DatabaseInterface  $db  Database driver.
	 *
	 * @return  array  Array with installed language packs in admin and site area.
	 *
	 * @since   3.1
	 */
	public function getLocaliseAdmin(DatabaseInterface $db = null)
	{
		$langfiles = array();

		// If db connection, fetch them from the database.
		if ($db)
		{
			foreach (LanguageHelper::getInstalledLanguages() as $clientId => $language)
			{
				$clientName = $clientId === 0 ? 'site' : 'admin';

				foreach ($language as $languageCode => $lang)
				{
					$langfiles[$clientName][] = $lang->element;
				}
			}
		}
		// Read the folder names in the site and admin area.
		else
		{
			$langfiles['site']  = \JFolder::folders(LanguageHelper::getLanguagePath(JPATH_SITE));
			$langfiles['admin'] = \JFolder::folders(LanguageHelper::getLanguagePath(JPATH_ADMINISTRATOR));
		}

		return $langfiles;
	}

	/**
	 * Gets the name of the current template.
	 *
	 * @param   boolean  $params  True to return the template parameters
	 *
	 * @return  string  The name of the template.
	 *
	 * @since   3.1
	 */
	public function getTemplate($params = false)
	{
		if ($params)
		{
			$template = new \stdClass;
			$template->template = 'template';
			$template->params = new Registry;

			return $template;
		}

		return 'template';
	}

	/**
	 * Initialise the application.
	 *
	 * @param   array  $options  An optional associative array of configuration settings.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function initialiseApp($options = array())
	{
		// Get the localisation information provided in the localise.xml file.
		$forced = $this->getLocalise();

		// Check the request data for the language.
		if (empty($options['language']))
		{
			$requestLang = $this->input->getCmd('lang', null);

			if ($requestLang !== null)
			{
				$options['language'] = $requestLang;
			}
		}

		// Check the session for the language.
		if (empty($options['language']))
		{
			$sessionOptions = $this->getSession()->get('setup.options');

			if (isset($sessionOptions['language']))
			{
				$options['language'] = $sessionOptions['language'];
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
				$options['language'] = LanguageHelper::detectLanguage();

				if (empty($options['language']))
				{
					$options['language'] = 'en-GB';
				}
			}
		}

		// Give the user English.
		if (empty($options['language']))
		{
			$options['language'] = 'en-GB';
		}

		// Check for custom helpurl.
		if (empty($forced['helpurl']))
		{
			$options['helpurl'] = 'https://help.joomla.org/proxy?keyref=Help{major}{minor}:{keyref}&lang={langcode}';
		}
		else
		{
			$options['helpurl'] = $forced['helpurl'];
		}

		// Store helpurl in the session.
		$this->getSession()->set('setup.helpurl', $options['helpurl']);

		// Set the language in the class.
		$this->config->set('language', $options['language']);
		$this->config->set('debug_lang', $forced['debug']);
		$this->config->set('sampledata', $forced['sampledata']);
		$this->config->set('helpurl', $options['helpurl']);
	}

	/**
	 * Allows the application to load a custom or default document.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create a document,
	 * if required, based on more specific needs.
	 *
	 * @param   Document  $document  An optional document object. If omitted, the factory document is created.
	 *
	 * @return  InstallationApplication This method is chainable.
	 *
	 * @since   3.2
	 */
	public function loadDocument(Document $document = null)
	{
		if ($document === null)
		{
			$lang = Factory::getLanguage();
			$type = $this->input->get('format', 'html', 'word');
			$date = new Date('now');

			$attributes = array(
				'charset'      => 'utf-8',
				'lineend'      => 'unix',
				'tab'          => "\t",
				'language'     => $lang->getTag(),
				'direction'    => $lang->isRtl() ? 'rtl' : 'ltr',
				'mediaversion' => md5($date->format('YmdHi')),
			);

			$document = $this->getContainer()->get(FactoryInterface::class)->createDocument($type, $attributes);

			// Register the instance to Factory.
			Factory::$document = $document;
		}

		$this->document = $document;

		return $this;
	}

	/**
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the application response buffer.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function render()
	{
		$options = [];

		if ($this->document instanceof HtmlDocument)
		{
			$file = $this->input->getCmd('tmpl', 'index');

			$options = [
				'template'  => 'template',
				'file'      => $file . '.php',
				'directory' => JPATH_THEMES,
				'params'    => '{}',
			];
		}

		// Parse the document.
		$this->document->parse($options);

		// Render the document.
		$data = $this->document->render($this->get('cache_enabled'), $options);

		// Set the application output data.
		$this->setBody($data);
	}

	/**
	 * Set configuration values.
	 *
	 * @param   array   $vars       Array of configuration values
	 * @param   string  $namespace  The namespace
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function setCfg(array $vars = array(), $namespace = 'config')
	{
		$this->config->loadArray($vars, $namespace);
	}

	/**
	 * Returns the application \JMenu object.
	 *
	 * @param   string  $name     The name of the application/client.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  AbstractMenu
	 *
	 * @since   3.2
	 */
	public function getMenu($name = null, $options = array())
	{
		return null;
	}
}
