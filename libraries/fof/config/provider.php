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
 */
class FOFConfigProvider
{
	/**
	 * Cache of FOF components' configuration variables
	 *
	 * @var array
	 */
	static $configurations = array();

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

		static $isCli, $isAdmin;

		if (is_null($isCli))
		{
			list ($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		}

		if ($isCli)
		{
			$order = array('cli', 'backend');
		}
		elseif ($isAdmin)
		{
			$order = array('backend');
		}
		else
		{
			$order = array('frontend');
		}

		$order = array_reverse($order);
		self::$configurations[$component] = array();
		foreach ($order as $area)
		{
			$config = $this->parseComponentArea($component, $area);
			self::$configurations[$component] = array_merge_recursive(self::$configurations[$component], $config);
		}
	}

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

		// Check that the path exists
		JLoader::import('joomla.filesystem.folder');
		$path = JPATH_ADMINISTRATOR . '/components/' . $component;
		$path = JPath::check($path);
		if (!JFolder::exists($path))
		{
			return $ret;
		}

		// Read the filename if it exists
		$filename = $path . '/fof.xml';
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

	/**
	 * Internal method to parse the view configuration data
	 *
	 * @param   array  $viewData  The data to parse
	 *
	 * @return  array  Parsed data
	 */
	protected function parseViews(array $viewData)
	{
		$ret = array();

		if (empty($viewData))
		{
			return $ret;
		}

		foreach($viewData as $aView)
		{
			$key = (string)$aView['name'];

			// Parse ACL options
			$ret[$key]['acl'] = array();
			$aclData = $aView->xpath('acl/task');
			if (!empty($aclData))
			{
				foreach($aclData as $acl)
				{
					$k = (string)$acl['name'];
					$ret[$key]['acl'][$k] = (string)$acl;
				}
			}

			// Parse taskmap
			$ret[$key]['taskmap'] = array();
			$taskmapData = $aView->xpath('taskmap/task');
			if (!empty($taskmapData))
			{
				foreach($taskmapData as $map)
				{
					$k = (string)$map['name'];
					$ret[$key]['taskmap'][$k] = (string)$map;
				}
			}
		}

		return $ret;
	}


}