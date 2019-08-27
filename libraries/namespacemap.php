<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

/**
 * Class JNamespaceMap
 *
 * @since  4.0.0
 */
class JNamespacePsr4Map
{
	/**
	 * Path to the autoloader
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $file = JPATH_LIBRARIES . '/autoload_psr4.php';

	/**
	 * Check if the file exists
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function exists()
	{
		return file_exists($this->file);
	}

	/**
	 * Check if the namespace mapping file exists, if not create it
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function ensureMapFileExists()
	{
		if (!$this->exists())
		{
			$this->create();
		}
	}

	/**
	 * Create the namespace file
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function create()
	{
		$extensions = $this->getNamespaces('administrator/components');
		$extensions = array_merge($extensions, $this->getNamespaces('api/components'));
		$extensions = array_merge($extensions, $this->getNamespaces('modules'));
		$extensions = array_merge($extensions, $this->getNamespaces('administrator/modules'));

		foreach (Folder::folders(JPATH_ROOT . '/plugins') as $pluginGroup)
		{
			$extensions = array_merge($extensions, $this->getNamespaces('/plugins/' . $pluginGroup));
		}

		$this->writeNamespaceFile($extensions);

		return true;
	}

	/**
	 * Load the PSR4 file
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function load()
	{
		if (!$this->exists())
		{
			$this->create();
		}

		$map = require $this->file;

		$loader = include JPATH_LIBRARIES . '/vendor/autoload.php';

		foreach ($map as $namespace => $path)
		{
			$loader->setPsr4($namespace, $path);
		}

		return true;
	}

	/**
	 * Write the Namespace mapping file
	 *
	 * @param   array  $elements  Array of elements
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function writeNamespaceFile($elements)
	{
		$content   = array();
		$content[] = "<?php";
		$content[] = 'defined(\'_JEXEC\') or die;';
		$content[] = 'return [';

		foreach ($elements as $namespace => $path)
		{
			$content[] = "\t'" . $namespace . "'" . ' => [' . $path . '],';
		}

		$content[] = '];';

		File::write($this->file, implode("\n", $content));
	}

	/**
	 * Get an array of namespaces with their respective path for the given extension directory.
	 *
	 * @param   string  $dir  The directory
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	private function getNamespaces(string $dir): array
	{
		// If it is not a dir return
		if (!is_dir(JPATH_ROOT . '/' . $dir))
		{
			return [];
		}

		// The extensions
		$extensions = [];

		// Loop over the extension type directory
		foreach (Folder::folders(JPATH_ROOT . '/' . $dir) as $extension)
		{
			// If it is a file we can't handle, ignore it
			if (strpos($extension, 'mod_') !== 0 && strpos($extension, 'com_') !== 0 && strpos($dir, '/plugins/') !== 0)
			{
				continue;
			}

			// Compile the extension path
			$extensionPath = JPATH_ROOT . '/' . $dir . '/' . $extension . '/';

			// Strip the com_ from the extension name for components
			$name = str_replace('com_', '', $extension, $count);
			$file = $extensionPath . $name . '.xml';

			// If there is no manifest file, ignore. If it was a component check if the xml was named with the com_
			// prefix.
			if (!file_exists($file))
			{
				if (!$count)
				{
					continue;
				}

				$file = $extensionPath . $extension . '.xml';

				if (!file_exists($file))
				{
					continue;
				}
			}

			// Load the manifest file
			$xml = simplexml_load_file($file);

			// When invalid, ignore
			if (!$xml)
			{
				continue;
			}

			// The namespace node
			$namespaceNode = $xml->namespace;

			// The namespace string
			$namespace = (string) $namespaceNode;

			// Ignore when the string is empty
			if (!$namespace)
			{
				continue;
			}

			// The namespace path
			$namespacePath = '/' . $dir . '/' . $extension . '/';

			// Normalize the namespace string
			$namespace = str_replace('\\', '\\\\', $namespace) . '\\\\';
			$path = str_replace('administrator/', '', $namespacePath);

			// Add the site path when a component
			if (strpos($extension, 'com_') === 0)
			{
				$extensions[$namespace . 'Site\\\\'] = 'JPATH_SITE . \'' . $path . $namespaceNode->attributes()->path . '\'';

				if (is_dir(JPATH_API . $path))
				{
					$extensions[$namespace . 'Api\\\\'] = 'JPATH_API . \'' . $path . $namespaceNode->attributes()->path . '\'';
				}
			}

			// Add the application specific segment when not a plugin
			if (strpos($dir, '/plugins/') !== 0)
			{
				$baseDir    = strpos($namespacePath, 'administrator/') ? 'JPATH_ADMINISTRATOR . \'' : 'JPATH_SITE . \'';
				$namespace .= strpos($namespacePath, 'administrator/') ? 'Administrator\\\\' : 'Site\\\\';
			}
			else
			{
				// Start in the plugin directory and remove the plugin prefix from the path
				$baseDir = 'JPATH_PLUGINS . \'';
				$path    = substr($path, 9);
			}

			// Set the namespace
			$extensions[$namespace] = $baseDir . $path . $namespaceNode->attributes()->path . '\'';
		}

		// Return the namespaces
		return $extensions;
	}
}
