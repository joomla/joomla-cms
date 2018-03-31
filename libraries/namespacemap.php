<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
	 * @return  bool
	 *
	 * @since   4.0.0
	 */
	public function exists()
	{
		if (!file_exists($this->file))
		{
			return false;
		}

		return true;
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
		// Ensure that the database is connected (because it isn't in the installer where this function gets called from
		// CMSApplication
		if (!$this->exists() && JFactory::getDbo()->connected())
		{
			$this->create();
		}
	}

	/**
	 * Create the namespace file
	 *
	 * @return  bool
	 *
	 * @since   4.0.0
	 */
	public function create()
	{
		$extensions = $this->getNamespacedExtensions();

		$elements = array();

		foreach ($extensions as $extension)
		{
			$element       = $extension->element;
			$baseNamespace = str_replace("\\", "\\\\", $extension->namespace);

			if (file_exists(JPATH_ADMINISTRATOR . '/components/' . $element))
			{
				$elements[$baseNamespace . '\\\\Administrator\\\\'] = array('/administrator/components/' . $element);
			}

			if (file_exists(JPATH_ROOT . '/components/' . $element))
			{
				$elements[$baseNamespace . '\\\\Site\\\\'] = array('/components/' . $element);
			}
		}

		$this->writeNamespaceFile($elements);

		return true;
	}

	/**
	 * Load the PSR4 file
	 *
	 * @return  bool
	 *
	 * @since   4.0.0
	 */
	public function load()
	{
		if (!$this->exists())
		{
			// We can't continue here
			if (!JFactory::getDbo()->connected())
			{
				return false;
			}

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
		$content[] = 'return array(';

		foreach ($elements as $namespace => $paths)
		{
			$pathString = '';

			foreach ($paths as $path)
			{
				$pathString .= '"' . $path . '",';
			}

			$content[] = "\t'" . $namespace . "'" . ' => array(JPATH_ROOT . ' . $pathString . '),';
		}

		$content[] = ');';

		file_put_contents($this->file, implode("\n", $content));
	}

	/**
	 * Get all namespaced extensions from the database
	 *
	 * @return  mixed|false
	 *
	 * @since   4.0.0
	 */
	protected function getNamespacedExtensions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('extension_id', 'element', 'namespace')))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('namespace') . ' IS NOT NULL AND ' . $db->quoteName('namespace') . ' != ""');

		$db->setQuery($query);

		$extensions = $db->loadObjectList();

		return $extensions;
	}
}
