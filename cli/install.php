<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * TODO description
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Bootstrap the application
if (!file_exists(dirname(__DIR__) . '/installation/application/bootstrap.php'))
{
	die("Installation application has been removed.\n");
}

require_once dirname(__DIR__) . '/installation/application/bootstrap.php';
chdir(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'installation');

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

/**
 * TODO description
 *
 * @package  Joomla.Cli
 * @since    3.4
 */
class JApplicationCliInstaller extends JApplicationCli
{
	/**
	 * Get a list of command-line options
	 *
	 * @return array each item is keyed by the installation system's internal option name; values arrays with keys:
	 *	 - getopt: string, a specification for use with getopt
	 *	 - required: bool, an indication of whether the value is required
	 *	 - default: mixed, default value to use if none is provided
	 *	 - factory: callable, a fnction which produces the default value
	 *
	 * @since  3.4
	 */
	public function getOptionsMetadata()
	{
		$optionsMetadata = array(
			'help' => array(
				'getopt'      => 'help',
				'description' => 'Display help',
			),
			'admin_email'         => array(
				'getopt'      => 'admin-email:',
				'description' => 'Admin user\'s email',
				'required'    => true,
			),
			'admin_password' => array(
				'getopt'      => 'admin-pass:',
				'description' => 'Admin user\'s password',
				'required'    => true,
			),
			'admin_user' => array(
				'getopt'      => 'admin-user:',
				'description' => 'Admin user\'s username',
				'default'     => 'admin',
			),
			'db_host' => array(
				'getopt'      => 'db-host:',
				'description' => 'Hostname (or hostname:port)',
				'default'     => 'localhost',
			),
			'db_name' => array(
				'getopt'      => 'db-name:',
				'description' => 'Database name',
				'required'    => true,
			),
			'db_old' => array(
				'getopt'      => 'db-old:',
				'description' => 'Policy to use with old DB [remove,backup]]',
				'default'     => 'backup',
			),
			'db_pass' => array(
				'getopt'      => 'db-pass:',
				'description' => 'Database password',
				'required'    => true,
			),
			'db_prefix' => array(
				'getopt'      => 'db-prefix:',
				'description' => 'Table prefix',
				'factory'     => function () {
					// FIXME: Duplicated from installation/model/fields/prefix.php
					$size    = 5;
					$prefix  = '';
					$chars   = range('a', 'z');
					$numbers = range(0, 9);

					// We want the fist character to be a random letter:
					shuffle($chars);
					$prefix .= $chars[0];

					// Next we combine the numbers and characters to get the other characters:
					$symbols = array_merge($numbers, $chars);
					shuffle($symbols);

					for ($i = 0, $j = $size - 1; $i < $j; ++$i)
					{
						$prefix .= $symbols[$i];
					}

					// Add in the underscore:
					$prefix .= '_';

					return $prefix;
				},
			),
			'db_type' => array(
				'getopt'      => 'db-type:',
				'description' => 'Database type [mysql,mysqli,postgresql,sqlsrv,sqlazure]',
				'default'     => 'mysqli',
			),
			'db_user' => array(
				'getopt'      => 'db-user:',
				'description' => 'Database user',
				'required'    => true,
			),
			'helpurl' => array(
				'getopt'      => 'help-url:',
				'description' => 'Help URL',
				'default'     => 'http://help.joomla.org/proxy/index.php?option=com_help&amp;keyref=Help{major}{minor}:{keyref}',
			),
			// FIXME: Not clear if this is useful. Seems to be "the language of the installation application"
			// and not "the language of the installed CMS"
			'language' => array(
				'getopt'      => 'lang:',
				'description' => 'Language',
				'default'     => 'en-GB',
			),
			'site_metadesc' => array(
				'getopt'      => 'desc:',
				'description' => 'Site description',
				'default'     => ''
			),
			'site_name' => array(
				'getopt'      => 'name:',
				'description' => 'Site name',
				'default'     => 'Joomla'
			),
			'site_offline' => array(
				'getopt'      => 'offline',
				'description' => 'Set site as offline',
				'default'     => 0,
			),
			'sample_file' => array(
				'getopt'      => 'sample:',
				'description' => 'Sample SQL file (sample_blog.sql,sample_brochure.sql,...)',
				'default'     => '',
			),
			'summary_email' => array(
				'getopt'      => 'email',
				'description' => 'Send email notification',
				'default'     => 0,
			),
		);

		// Installer internally has an option "admin_password2", but it
		// doesn't seem to be necessary.

		foreach (array_keys($optionsMetadata) as $key)
		{
			if (!isset($optionsMetadata[$key]['syntax']))
			{
				if (preg_match('/:$/', $optionsMetadata[$key]['getopt']))
				{
					$optionsMetadata[$key]['syntax'] = '--' . rtrim($optionsMetadata[$key]['getopt'], ':') . '="..."';
				}
				else
				{
					$optionsMetadata[$key]['syntax'] = '--' . $optionsMetadata[$key]['getopt'];
				}
			}
		}

		return $optionsMetadata;
	}

	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function doExecute()
	{
		JFactory::getApplication('CliInstaller');

		// Parse options
		$options = $this->parseOptions();

		if (array_key_exists('help', $options))
		{
			$this->displayUsage();
			$this->close(0);
		}

		$errors = $this->validateOptions($options);

		if (!empty($errors))
		{
			foreach ($errors as $error)
			{
				$this->enqueueMessage($error, 'fatal');
			}

			$this->displayUsage();
			$this->close(1);
		}

		// Attempt to initialise the database.
		$db = new InstallationModelDatabase;

		if (!$db->createDatabase($options))
		{
			$this->fatal("Error executing createDatabase");
		}

		// FIXME InstallationModelDatabase relies on session manipulation which doesn't work well in cli
		// $session = JFactory::getSession();
		// $options = $session->get('setup.options', NULL);
		$options['db_created'] = 1;
		$options['db_select']  = 1;

		if ($options['db_old'] == 'backup')
		{
			if (!$db->handleOldDatabase($options))
			{
				$this->fatal("Error executing handleOldDatabase");
			}
		}

		if (!$db->createTables($options))
		{
			$this->fatal("Error executing createTables");
		}

		// Attempt to setup the configuration.
		$configuration = new InstallationModelConfiguration;

		if (!$configuration->setup($options))
		{
			$this->fatal("Error executing setup");
		}

		// Attempt to create the database tables.
		if ($options['sample_file'])
		{
			if (!$db->installSampleData($options))
			{
				$this->fatal("Error executing installSampleData");
			}
		}

		$this->out('Done');
	}

	/**
	 * Display help text
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function displayUsage()
	{
		$this->out("Install Joomla");
		$this->out("usage: php install.php [options]");

		foreach ($this->getOptionsMetadata() as $spec)
		{
			$syntax = sprintf("%-25s", $spec['syntax']);

			if (isset($spec['description']))
			{
				$syntax .= $spec['description'];
			}

			if (isset($spec['required']) && $spec['required'])
			{
				$syntax .= ' (required)';
			}

			if (isset($spec['default']))
			{
				$syntax .= " (default: {$spec['default']})";
			}

			if (isset($spec['factory']))
			{
				$syntax .= " (default: auto-generated)";
			}

			$this->out("	" . $syntax);
		}
	}

	/**
	 * Validate the inputs
	 *
	 * @param   array  $options  parsed input values
	 *
	 * @return  array  An array of error messages
	 *
	 * @since   3.4
	 */
	public function validateOptions($options)
	{
		$optionsMetadata = $this->getOptionsMetadata();
		$errors = array();

		foreach ($optionsMetadata as $key => $spec)
		{
			if (!isset($options[$key]) && isset($spec['required']) && $spec['required'])
			{
				$errors[] = "Missing required option: {$spec['syntax']}";
			}
		}

		return $errors;
	}

	/**
	 * Parse all options from the command-line
	 *
	 * @return array
	 *
	 * @since  3.4
	 */
	public function parseOptions()
	{
		global $argv;

		if (count($argv) <= 1)
		{
			return array('help' => 1);
		}

		$optionsMetadata = $this->getOptionsMetadata();
		$longopts        = array();

		foreach ($optionsMetadata as $key => $spec)
		{
			$longopts[] = $spec['getopt'];
		}

		$rawOptions = getopt("", $longopts);
		$options    = array();

		foreach ($optionsMetadata as $key => $spec)
		{
			$rawKey = rtrim($spec['getopt'], ':');

			if (isset($rawOptions[$rawKey]))
			{
				if (preg_match('/:$/', $spec['getopt']))
				{
					$options[$key] = $rawOptions[$rawKey];
				}
				else
				{
					$options[$key] = 1;
				}
			}
			elseif (isset($spec['factory']))
			{
				$options[$key] = call_user_func($spec['factory']);
			}
			elseif (isset($spec['default']))
			{
				$options[$key] = $spec['default'];
			}
		}

		return $options;
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is message.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		$this->out("[$type] $msg");
	}

	/**
	 * Trigger a fatal error
	 *
	 * @param   string  $msg  The message to enqueue.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function fatal($msg)
	{
		$this->enqueueMessage($msg, 'fatal');
		$this->close(1);
	}

	/**
	 * FIXME: Duplicated from ./installation/application/web.php
	 */
	public function getLocaliseAdmin($db = false)
	{
		// Read the files in the admin area
		$path               = JLanguage::getLanguagePath(JPATH_ADMINISTRATOR);
		$langfiles['admin'] = JFolder::folders($path);

		// Read the files in the site area
		$path              = JLanguage::getLanguagePath(JPATH_SITE);
		$langfiles['site'] = JFolder::folders($path);

		if ($db)
		{
			$langfiles_disk     = $langfiles;
			$langfiles          = array();
			$langfiles['admin'] = array();
			$langfiles['site']  = array();

			$query = $db->getQuery(true)
				->select($db->quoteName(array('element', 'client_id')))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('language'));
			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				switch ($lang->client_id)
				{
					// Site
					case 0 :
						if (in_array($lang->element, $langfiles_disk['site']))
						{
							$langfiles['site'][] = $lang->element;
						}

						break;

					// Administrator
					case 1 :
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

JApplicationCli::getInstance('JApplicationCliInstaller')->execute();
