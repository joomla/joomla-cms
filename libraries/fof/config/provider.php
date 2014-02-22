<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  config
 *  @copyright   Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license     GNU General Public License version 2, or later
 */

defined('FOF_INCLUDED') or die();

/**
 * Reads and parses the fof.xml file in the back-end of a FOF-powered component,
 * provisioning the data to the rest of the FOF framework
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFConfigProvider
{
	/**
	 * Cache of FOF components' configuration variables
	 *
	 * @var array
	 */
	public static $configurations = array();

	/**
	 * Parses the configuration of the specified component
	 *
	 * @param   string   $component  The name of the component, e.g. com_foobar
	 * @param   boolean  $force      Force reload even if it's already parsed?
	 *
	 * @return  void
	 */
	public function parseComponent($component, $force = false)
	{
		if (!$force && isset(self::$configurations[$component]))
		{
			return;
		}

		if (FOFPlatform::getInstance()->isCli())
		{
			$order = array('cli', 'backend');
		}
		elseif (FOFPlatform::getInstance()->isBackend())
		{
			$order = array('backend');
		}
		else
		{
			$order = array('frontend');
		}

		$order[] = 'common';

		$order = array_reverse($order);
		self::$configurations[$component] = array();

		foreach ($order as $area)
		{
			$config = $this->parseComponentArea($component, $area);
			self::$configurations[$component] = array_merge_recursive(self::$configurations[$component], $config);
		}
	}

	/**
	 * Returns the value of a variable. Variables use a dot notation, e.g.
	 * view.config.whatever where the first part is the domain, the rest of the
	 * parts specify the path to the variable.
	 *
	 * @param   string  $variable  The variable name
	 * @param   mixed   $default   The default value, or null if not specified
	 *
	 * @return  mixed  The value of the variable
	 */
	public function get($variable, $default = null)
	{
		static $domains = null;

		if (is_null($domains))
		{
			$domains = $this->getDomains();
		}

		list($component, $domain, $var) = explode('.', $variable, 3);

		if (!isset(self::$configurations[$component]))
		{
			$this->parseComponent($component);
		}

		if (!in_array($domain, $domains))
		{
			return $default;
		}

		$class = 'FOFConfigDomain' . ucfirst($domain);
		$o = new $class;

		return $o->get(self::$configurations[$component], $var, $default);
	}

	/**
	 * Parses the configuration options of a specific component area
	 *
	 * @param   string  $component  Which component's cionfiguration to parse
	 * @param   string  $area       Which area to parse (frontend, backend, cli)
	 *
	 * @return  array  A hash array with the configuration data
	 */
	protected function parseComponentArea($component, $area)
	{
		// Initialise the return array
		$ret = array();

		// Get the folders of the component
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($component);

		// Check that the path exists
		JLoader::import('joomla.filesystem.folder');
		$path = $componentPaths['admin'];
		$path = JPath::check($path);

		if (!JFolder::exists($path))
		{
			return $ret;
		}

		// Read the filename if it exists
		$filename = $path . '/fof.xml';
		JLoader::import('joomla.filesystem.file');

		if (!JFile::exists($filename))
		{
			return $ret;
		}

		$data = JFile::read($filename);

		// Load the XML data in a SimpleXMLElement object
		$xml = simplexml_load_string($data);

		if (!($xml instanceof SimpleXMLElement))
		{
			return $ret;
		}

		// Get this area's data
		$areaData = $xml->xpath('//' . $area);

		if (empty($areaData))
		{
			return $ret;
		}

		$xml = array_shift($areaData);

		// Parse individual configuration domains
		$domains = $this->getDomains();

		foreach ($domains as $dom)
		{
			$class = 'FOFConfigDomain' . ucfirst($dom);

			if (class_exists($class, true))
			{
				$o = new $class;
				$o->parseDomain($xml, $ret);
			}
		}

		// Finally, return the result
		return $ret;
	}

	/**
	 * Gets a list of the available configuration domain adapters
	 *
	 * @return  array  A list of the available domains
	 */
	protected function getDomains()
	{
		static $domains = array();

		if (empty($domains))
		{
			JLoader::import('joomla.filesystem.folder');
			$files = JFolder::files(__DIR__ . '/domain', '.php');

			if (!empty($files))
			{
				foreach ($files as $file)
				{
					$domain = basename($file, '.php');

					if ($domain == 'interface')
					{
						continue;
					}

					$domain = preg_replace('/[^A-Za-z0-9]/', '', $domain);
					$domains[] = $domain;
				}

				$domains = array_unique($domains);
			}
		}

		return $domains;
	}
}
