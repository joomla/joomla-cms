<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') || die();

class plgSystemBackuponupdate extends CMSPlugin
{
	/** @var \Joomla\CMS\Application\AdministratorApplication */
	public $app;

	private $isEnabled;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since   3.8.0
	 */
	public function __construct(&$subject, $config)
	{
		/**
		 * I know that this piece of code cannot possibly be executed since I have already returned BEFORE declaring
		 * the class when eAccelerator is detected. However, eAccelerator is a GINORMOUS, STINKY PILE OF BULL CRAP. The
		 * stupid thing will return above BUT it will also declare the class EVEN THOUGH according to how PHP works
		 * this part of the code should be unreachable o_O Therefore I have to define this constant and exit the
		 * constructor when we have already determined that this class MUST NOT be defined. Because screw you
		 * eAccelerator, that's why.
		 */
		if (defined('AKEEBA_EACCELERATOR_IS_SO_BORKED_IT_DOES_NOT_EVEN_RETURN'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Runs on application initialization. Implements the functionality of this plugin.
	 *
	 * @return  void
	 * @since   3.8.0
	 */
	public function onAfterInitialise()
	{
		// Make sure this is the back-end
		try
		{
			$app = Factory::getApplication();
		}
		catch (Exception $e)
		{
			return;
		}

		if (!$app->isClient('administrator'))
		{
			return;
		}

		// Make sure we are enabled
		if (!$this->isEnabled())
		{
			return;
		}

		// Handle the flag toggle through AJAX
		$ji          = Factory::getApplication()->input;
		$toggleParam = $ji->getCmd('_akeeba_backup_on_update_toggle');

		if ($toggleParam && ($toggleParam == Factory::getSession()->getToken()))
		{
			$this->toggleBoUFlag();

			return;
		}

		// Make sure we are active
		if ($this->getBoUFlag() != 1)
		{
			return;
		}

		// Get the input variables
		$component = $ji->getCmd('option', '');
		$task      = $ji->getCmd('task', '');
		$backedup  = $ji->getInt('is_backed_up', 0);

		// Perform a redirection on Joomla! Update download or install task, unless we have already backed up the site
		$redirectCondition = ($component == 'com_joomlaupdate') && ($task == 'update.install') && !$backedup;

		if ($redirectCondition)
		{
			// Get the backup profile ID
			$profileId = (int) $this->params->get('profileid', 1);

			if ($profileId <= 0)
			{
				$profileId = 1;
			}

			// Get the description override
			$this->loadLanguage();
			$description = $this->preprocessDescription($this->params->get(
				'description',
				Text::_('PLG_SYSTEM_BACKUPONUPDATE_DEFAULT_DESCRIPTION')
			));

			$jtoken = Factory::getSession()->getFormToken();

			// Get the return URL
			$returnUri = new Uri(Uri::base() . 'index.php');
			$params    = [
				'option'       => 'com_joomlaupdate',
				'task'         => 'update.install',
				'is_backed_up' => 1,
				$jtoken        => 1,
			];
			array_walk($params, function ($value, $key) use (&$returnUri) {
				$returnUri->setVar($key, $value);
			});

			// Get the redirect URL
			$redirectUri = new Uri(Uri::base() . 'index.php');
			$params      = [
				'option'      => 'com_akeeba',
				'view'        => 'Backup',
				'autostart'   => 1,
				'returnurl'   => base64_encode($returnUri->toString()),
				'description' => urlencode($description),
				'profileid'   => $profileId,
				$jtoken       => 1,
			];
			array_walk($params, function ($value, $key) use (&$redirectUri) {
				$redirectUri->setVar($key, $value);
			});

			// Perform the redirection
			$app->redirect($redirectUri->toString());
		}
	}

	/**
	 * Renders the Backup on Update status icon in the Joomla! backend.
	 *
	 * We use a bit of fine trickery to accomplish that. The onAfterModuleList event is triggered after Joomla! has
	 * loaded a list of the modules to render on the page. We use that event to inject a fake module object of type
	 * mod_custom with the HTML we want to render in the 'status' position of the template.
	 *
	 * @param   array  $modules  The array of module objects passed to us by Joomla!
	 *
	 * @throws  Exception
	 * @since   5.4.1
	 */
	public function onAfterModuleList(&$modules)
	{
		$app = $this->app;

		// Only work when format=html (since we try adding CSS and Javascript on the page which is only valid in HTML).
		if ($app->input->getCmd('format', 'html') != 'html')
		{
			return;
		}

		// Am I in the administrator application to begin with?
		$isAdmin = $app->isClient('administrator');

		if (!$isAdmin)
		{
			return;
		}

		// Make sure we are enabled
		if (!$this->isEnabled())
		{
			return;
		}

		// Is the main menu supposed to be hidden?
		if ($this->app->input->getBool('hidemainmenu', false))
		{
			return;
		}

		// Load the language
		$this->loadLanguage();

		try
		{
			HTMLHelper::_('bootstrap.popover');

			/**
			 * Apparently you may have format=html with an application that returns no document...?! I can't see how it's
			 * possible lest a 3PD has screwed up. In any case, this happened in tickets 28218, 28223, 28224 and 28225. My
			 * workaround is to first check if the application can and does return a document. If not, try to get the document
			 * via Factory (legacy method). If that fails too, skip the "Disable plugin" feature altogether.
			 */
			$document = null;

			if (method_exists($app, 'getDocument'))
			{
				$document = $app->getDocument();
			}

			if (is_null($document) || !method_exists($document, 'addStyleDeclaration'))
			{
				/**
				 * Don't remove the class_exists. Joomla! 3.8 will have JFactor as an alias to a namespaced class so I might
				 * need to load it with the class_exists trick. As for the method_exists, it's us trying to make sure future
				 * versions of Joomla! won't break anything.
				 */
				if (class_exists('Joomla\CMS\Factory', true) && method_exists(Factory::class, 'getDocument'))
				{
					$document = Factory::getDocument();
				}
			}

			/**
			 * Now, if the document is still unset (a 3PD seriously cocked up a JApplicationCms subclass) OR the document is
			 * quite obviously not JDocumentHtml (which means a 3PD should be tarred, feathered and stringed for cocking up an
			 * application AND a document subclass) we have to skip our "Disable plugin" feature since it, well, not work at
			 * all.
			 */
			if (is_null($document) || !method_exists($document, 'addStyleDeclaration'))
			{
				return;
			}

			$isJoomla4        = version_compare(JVERSION, '3.999999.999999', 'gt');
			$baseDocumentName = $isJoomla4 ? 'joomla4' : 'default';

			$document->addStyleDeclaration($this->loadTemplate($baseDocumentName . '.css'));

			$fakeModule = (object) [
				'id'        => -1,
				'title'     => 'Backup on Update',
				'module'    => 'mod_custom',
				'position'  => 'status',
				'content'   => $this->loadTemplate($baseDocumentName . '.html', [
					'active' => $this->getBoUFlag(),
				]),
				'showtitle' => 0,
				'params'    => '{"prepare_content":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","cache_time":"1","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',
				'menuid'    => 0,
			];
		}
		catch (Exception $e)
		{
			return;
		}


		$modules[] = $fakeModule;
	}

	/**
	 * Load a plugin layout file. These files can be overridden with standard Joomla! template overrides.
	 *
	 * @param   string  $layout  The layout file to load
	 * @param   array   $params  An array passed verbatim to the layout file as the `$params` variable
	 *
	 * @return  string  The rendered contents of the file
	 *
	 * @since   5.4.1
	 */
	private function loadTemplate($layout, array $params = [])
	{
		$file = PluginHelper::getLayoutPath('system', 'backuponupdate', $layout);

		ob_start();

		require_once $file;

		$ret = ob_get_clean();

		return $ret;
	}

	/**
	 * Get the Backup on Update flag
	 *
	 * @return  int
	 * @since   5.5.0
	 */
	private function getBoUFlag()
	{
		return Factory::getSession()->get('active', 1, 'plg_system_backuponupdate');
	}

	/**
	 * Toggle the Backup on Update flag
	 *
	 * @return  void
	 * @since   5.5.0
	 */
	private function toggleBoUFlag()
	{
		$status = 1 - $this->getBoUFlag();

		Factory::getSession()->set('active', $status, 'plg_system_backuponupdate');
	}

	/**
	 * Should this plugin be enabled at all?
	 *
	 * @return  bool
	 * @since   7.0.0
	 */
	private function isEnabled()
	{
		if (!is_null($this->isEnabled))
		{
			return $this->isEnabled;
		}

		$this->isEnabled = false;

		if (!version_compare(PHP_VERSION, '7.1.0', '>='))
		{
			return false;
		}

		// Make sure Akeeba Backup is installed
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba'))
		{
			return false;
		}

		// Is Akeeba Backup enabled?
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->qn('enabled'))
				->from($db->qn('#__extensions'))
				->where($db->qn('element') . ' = ' . $db->q('com_akeeba'))
				->where($db->qn('type') . ' = ' . $db->q('component'));
			$db->setQuery($query);
			$enabled         = $db->loadResult();
			$this->isEnabled = is_null($enabled) ? false : ((bool) $enabled);
		}
		catch (Exception $e)
		{
			$this->isEnabled = false;
		}

		return $this->isEnabled;
	}

	/**
	 * Returns the version number of the latest Joomla release.
	 *
	 * It will return the string "(???)" if no Joomla update is being listed
	 *
	 * @return  string
	 * @since   7.0.0
	 */
	private function getLatestJoomlaVersion()
	{
		$latestVersion = '(???)';

		// Get the extension ID for Joomla! itself (the files_joomla pseudo-extension)
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->qn('extension_id'))
				->from($db->qn('#__extensions'))
				->where($db->qn('name') . ' = ' . $db->q('files_joomla'));

			$jEid = $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			$jEid = 700;
		}

		if (is_null($jEid) || ($jEid <= 0))
		{
			$jEid = 700;
		}

		// Fetch the Joomla update information from the database.
		try
		{
			$db           = Factory::getDbo();
			$query        = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__updates'))
				->where($db->quoteName('extension_id') . ' = ' . $db->quote($jEid));
			$updateObject = $db->setQuery($query)->loadObject();
		}
		catch (Exception $e)
		{
			return $latestVersion;
		}

		if (is_null($updateObject))
		{
			return $latestVersion;
		}

		return $updateObject->version;
	}

	/**
	 * Pre
	 *
	 * @param $description
	 *
	 * @return string|string[]
	 */
	private function preprocessDescription($description)
	{
		$replacements = [
			'[VERSION_FROM]' => JVERSION,
			'[VERSION_TO]'   => $this->getLatestJoomlaVersion(),
		];

		return str_replace(array_keys($replacements), array_values($replacements), $description);
	}
}
