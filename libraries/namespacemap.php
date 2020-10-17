<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
	protected $file = JPATH_CACHE . '/autoload_psr4.php';

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
		$extensions = array_merge(
			$this->getNamespaces('component'),
			$this->getNamespaces('module'),
			$this->getNamespaces('plugin'),
			$this->getNamespaces('library')
		);

		ksort($extensions);

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
	 * Get an array of namespaces with their respective path for the given extension type.
	 *
	 * @param   string  $type  The extension type
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	private function getNamespaces(string $type): array
	{
		if (!in_array($type, ['component', 'module', 'plugin', 'library'], true))
		{
			return [];
		}

		// Select directories containing extension manifest files.
		if ($type === 'component')
		{
			$directories = [JPATH_ADMINISTRATOR . '/components'];
		}
		elseif ($type === 'module')
		{
			$directories = [JPATH_SITE . '/modules', JPATH_ADMINISTRATOR . '/modules'];
		}
		elseif ($type === 'plugin')
		{
			$directories = Folder::folders(JPATH_PLUGINS, '.', false, true);
		}
		else
		{
			$directories = [JPATH_LIBRARIES];
		}

		$extensions = [];

		foreach ($directories as $directory)
		{
			foreach (Folder::folders($directory) as $extension)
			{
				// Compile the extension path
				$extensionPath = $directory . '/' . $extension . '/';

				// Strip the com_ from the extension name for components
				$name = str_replace('com_', '', $extension, $count);
				$file = $extensionPath . $name . '.xml';

				// If there is no manifest file, ignore. If it was a component check if the xml was named with the com_ prefix.
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

				// Normalize the namespace string
				$namespace     = str_replace('\\', '\\\\', $namespace) . '\\\\';
				$namespacePath = rtrim($extensionPath . $namespaceNode->attributes()->path, '/');

				if ($type === 'plugin' || $type === 'library')
				{
					$baseDir = $type === 'plugin' ? 'JPATH_PLUGINS . \'' : 'JPATH_LIBRARIES . \'';
					$path    = str_replace($type === 'plugin' ? JPATH_PLUGINS : JPATH_LIBRARIES, '', $namespacePath);

					// Set the namespace
					$extensions[$namespace] = $baseDir . $path . '\'';

					continue;
				}

				// Check if we need to use administrator path
				$isAdministrator = strpos($namespacePath, JPATH_ADMINISTRATOR) === 0;
				$path            = str_replace($isAdministrator ? JPATH_ADMINISTRATOR : JPATH_SITE, '', $namespacePath);

				// Add the site path when a component
				if ($type === 'component')
				{
					if (is_dir(JPATH_SITE . $path))
					{
						$extensions[$namespace . 'Site\\\\'] = 'JPATH_SITE . \'' . $path . '\'';
					}

					if (is_dir(JPATH_API . $path))
					{
						$extensions[$namespace . 'Api\\\\'] = 'JPATH_API . \'' . $path . '\'';
					}
				}

				// Add the application specific segment when a component or module
				$baseDir    = $isAdministrator ? 'JPATH_ADMINISTRATOR . \'' : 'JPATH_SITE . \'';
				$namespace .= $isAdministrator ? 'Administrator\\\\' : 'Site\\\\';

				// Set the namespace
				$extensions[$namespace] = $baseDir . $path . '\'';
			}
		}

		// Return the namespaces
		return $extensions;
	}
}
