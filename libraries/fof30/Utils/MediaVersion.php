<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils;

defined('_JEXEC') || die;

use Exception;
use FOF30\Container\Container;
use JDatabaseDriver;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Class MediaVersion
 * @package FOF30\Utils
 *
 * @since   3.5.3
 */
class MediaVersion
{
	/**
	 * Cached the version and date of FOF-powered components
	 *
	 * @var   array
	 * @since 3.5.3
	 */
	protected static $componentVersionCache = [];

	/**
	 * The current component's container
	 *
	 * @var   Container
	 * @since 3.5.3
	 */
	protected $container;

	/**
	 * The configured media query version
	 *
	 * @var   string|null;
	 * @since 3.5.3
	 */
	protected $mediaVersion;

	/**
	 * MediaVersion constructor.
	 *
	 * @param   Container  $c  The component container
	 *
	 * @since   3.5.3
	 */
	public function __construct(Container $c)
	{
		$this->container = $c;
	}

	/**
	 * Get a component's version and date
	 *
	 * @param   string           $component
	 * @param   JDatabaseDriver  $db
	 *
	 * @return  array
	 * @since   3.5.3
	 */
	protected static function getComponentVersionAndDate($component, $db)
	{
		if (array_key_exists($component, self::$componentVersionCache))
		{
			return self::$componentVersionCache[$component];
		}

		$version = '0.0.0';
		$date    = date('Y-m-d H:i:s');

		try
		{
			$query = $db->getQuery(true)
				->select([
					$db->qn('manifest_cache'),
				])->from($db->qn('#__extensions'))
				->where($db->qn('type') . ' = ' . $db->q('component'))
				->where($db->qn('name') . ' = ' . $db->q($component));

			$db->setQuery($query);

			$json = $db->loadResult();

			if (class_exists('JRegistry'))
			{
				$params = new Registry($json);
			}
			else
			{
				$params = new Registry($json);
			}

			$version = $params->get('version', $version);
			$date    = $params->get('creationDate', $date);
		}
		catch (Exception $e)
		{
		}

		self::$componentVersionCache[$component] = [$version, $date];

		return self::$componentVersionCache[$component];
	}

	/**
	 * Serialization helper
	 *
	 * This is for the benefit of legacy components which might use Joomla's JS/CSS inclusion directly passing
	 * $container->mediaVersion as the version argument. In FOF 3.5.2 and lower that was always string or null, making
	 * it a safe bet. In FOF 3.5.3 and later it's an object. It's not converted to a string until Joomla builds its
	 * template header. However, Joomla's cache system will try to serialize all CSS and JS definitions, including their
	 * parameters of which version is one. Therefore, for those legacy applications, Joomla would be trying to serialize
	 * the MediaVersion object which would try to serialize the container. That would cause an immediate failure since
	 * we protect the Container from being serialized.
	 *
	 * Our Template service knows about this and stringifies the MediaVersion before passing it to Joomla. Legacy apps
	 * may not do that. Using the __sleep and __wakeup methods in this class we make sure that we are essentially
	 * storing nothing but strings in the serialized representation and we reconstruct the container upon
	 * unseralization. That said, it's a good idea to use the Template service instead of $container->mediaVersion
	 * directly or, at the very least, use (string) $container->mediaVersion when using the Template service is not a
	 * viable option.
	 *
	 * @return  string[]
	 */
	public function __sleep()
	{
		$this->componentName = $this->container->componentName;

		return [
			'mediaVersion',
			'componentName',
		];
	}

	/**
	 * Unserialization helper
	 *
	 * @return  void
	 * @see     __sleep
	 */
	public function __wakeup()
	{
		if (isset($this->componentName))
		{
			$this->container = Container::getInstance($this->componentName);
		}
	}

	/**
	 * Returns the media query version string
	 *
	 * @return  string
	 * @since   3.5.3
	 */
	public function __toString()
	{
		if (empty($this->mediaVersion))
		{
			$this->mediaVersion = $this->getDefaultMediaVersion();
		}

		return $this->mediaVersion;
	}

	/**
	 * Sets the media query version string
	 *
	 * @param   mixed  $mediaVersion
	 *
	 * @since   3.5.3
	 */
	public function setMediaVersion($mediaVersion)
	{
		$this->mediaVersion = $mediaVersion;
	}

	/**
	 * Returns the default media query version string if none is already defined
	 *
	 * @return  string
	 * @since   3.5.3
	 */
	protected function getDefaultMediaVersion()
	{
		// Initialise
		[$version, $date] = self::getComponentVersionAndDate($this->container->componentName, $this->container->db);

		// Get the site's secret
		try
		{
			$app = Factory::getApplication();

			if (method_exists($app, 'get'))
			{
				$secret = $app->get('secret');
			}
		}
		catch (Exception $e)
		{
		}

		// Generate the version string
		return md5($version . $date . $secret);
	}
}
