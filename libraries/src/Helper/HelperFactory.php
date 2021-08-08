<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Helper;

\defined('_JEXEC') or die;

/**
 * Namespace based implementation of the HelperFactoryInterface
 *
 * @since  4.0.0
 */
class HelperFactory implements HelperFactoryInterface
{
	/**
	 * The extension namespace
	 *
	 * @var  string
	 *
	 * @since   4.0.0
	 */
	private $namespace;

	/**
	 * HelperFactory constructor.
	 *
	 * @param   string  $namespace  The namespace
	 *
	 * @since   4.0.0
	 */
	public function __construct(string $namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * Returns a helper instance for the given name.
	 *
	 * @param   string  $name    The name
	 * @param   array   $config  The config
	 *
	 * @return  \stdClass
	 *
	 * @since   4.0.0
	 */
	public function getHelper(string $name, array $config = [])
	{
		$className = '\\' . trim($this->namespace, '\\') . '\\' . $name;

		if (!class_exists($className))
		{
			return null;
		}

		return new $className($config);
	}
}
