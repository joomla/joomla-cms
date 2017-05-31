<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Class NamespaceMap
 *
 * @since  __DEPLOY_VERSION__
 */
class NamespaceMap
{
	protected static $file = JPATH_LIBRARIES . '/autoload_psr4.php';

	/**
	 * Check if the file is existing
	 *
	 * @return bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function exists()
	{
		if (!file_exists(self::$file))
		{
			return false;
		}

		return true;
	}

	/**
	 * Create the namespace file
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function create()
	{
		$extensions = self::getNamespacedExtensions();

		$elements = array();

		foreach ($extensions as $extension)
		{
			$element       = $extension->element;
			$baseNamespace = str_replace("\\", "\\\\", $extension->namespace);

			if (file_exists(JPATH_ADMINISTRATOR . '/components/' . $element))
			{
				$elements[$baseNamespace . '\\\\Administrator'] = array('/administrator/components/' . $element);
			}

			if (file_exists(JPATH_ROOT . '/components/' . $element))
			{
				$elements[$baseNamespace . '\\\\Site'] = array('/components/' . $element);
			}
		}

		// Set the configuration file path.
		$file = JPATH_LIBRARIES . '/autoload_psr4.php';

		// Attempt to write the configuration file as a PHP class named JConfig.
		// NOPE GEORGE
		// $registry = new Joomla\Registry\Registry($elements);
		// $autoloadCache = $registry->toString('PHP', array('closingtag' => false));

		$content   = array();
		$content[] = "<?php";
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

		file_put_contents($file, implode("\n", $content));

		return true;
	}

	/**
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	protected static function getNamespacedExtensions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select('extension_id, element, namespace')
			->from('#__extensions')
			->where('namespace IS NOT NULL AND namespace != ""');

		$db->setQuery($query);

		$extensions = $db->loadObjectList();

		return $extensions;
	}
}
