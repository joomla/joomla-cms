<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Changelog;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;

/**
 * Changelog class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Changelog extends CMSObject
{
	/**
	 * Update manifest `<element>` element
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $element;

	/**
	 * Update manifest `<type>` element
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type;

	/**
	 * Update manifest `<version>` element
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $version;

	/**
	 * Update manifest `<security>` element
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $security = array();

	/**
	 * Update manifest `<fix>` element
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $fix = array();

	/**
	 * Update manifest `<language>` element
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $language = array();

	/**
	 * Update manifest `<addition>` element
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $addition = array();

	/**
	 * Update manifest `<change>` elements
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $change = array();

	/**
	 * Update manifest `<remove>` element
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $remove = array();

	/**
	 * Update manifest `<maintainer>` element
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $note = array();

	/**
	 * Resource handle for the XML Parser
	 *
	 * @var    resource
	 * @since  __DEPLOY_VERSION__
	 */
	protected $xmlParser;

	/**
	 * Element call stack
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $stack = array('base');

	/**
	 * Object containing the current update data
	 *
	 * @var    \stdClass
	 * @since  __DEPLOY_VERSION__
	 */
	protected $currentUpdate;

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return  object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function _getStackLocation()
	{
		return implode('->', $this->stack);
	}

	/**
	 * Get the last position in stack count
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function _getLastTag()
	{
		return $this->stack[count($this->stack) - 1];
	}

	/**
	 * XML Start Element callback
	 *
	 * @param   object  $parser  Parser object
	 * @param   string  $name    Name of the tag found
	 * @param   array   $attrs   Attributes of the tag
	 *
	 * @return  void
	 *
	 * @note    This is public because it is called externally
	 * @since   1.7.0
	 */
	public function _startElement($parser, $name, $attrs = array())
	{
		$this->stack[] = $name;
		$tag           = $this->_getStackLocation();

		// Reset the data
		if (isset($this->$tag))
		{
			$this->$tag->_data = '';
		}

		$name = strtolower($name);

		if (!isset($this->currentUpdate->$name))
		{
			$this->currentUpdate->$name = new \stdClass;
		}

		$this->currentUpdate->$name->_data = '';

		foreach ($attrs as $key => $data)
		{
			$key = strtolower($key);
			$this->currentUpdate->$name->$key = $data;
		}
	}

	/**
	 * Callback for closing the element
	 *
	 * @param   object  $parser  Parser object
	 * @param   string  $name    Name of element that was closed
	 *
	 * @return  void
	 *
	 * @note    This is public because it is called externally
	 * @since   1.7.0
	 */
	public function _endElement($parser, $name)
	{
		array_pop($this->stack);

		switch ($name)
		{
			// Closing update, find the latest version and check
			case 'UPDATE':
				$product = strtolower(InputFilter::getInstance()->clean(Version::PRODUCT, 'cmd'));

				// Support for the min_dev_level and max_dev_level attributes is deprecated, a regexp should be used instead
				if (isset($this->currentUpdate->targetplatform->min_dev_level) || isset($this->currentUpdate->targetplatform->max_dev_level))
				{
					Log::add(
						'Support for the min_dev_level and max_dev_level attributes of an update\'s <targetplatform> tag is deprecated and'
						. ' will be removed in 4.0. The full version should be specified in the version attribute and may optionally be a regexp.',
						Log::WARNING,
						'deprecated'
					);
				}

				/*
				 * Check that the product matches and that the version matches (optionally a regexp)
				 *
				 * Check for optional min_dev_level and max_dev_level attributes to further specify targetplatform (e.g., 3.0.1)
				 */
				$patchVersion = $this->get('jversion.dev_level', Version::PATCH_VERSION);
				$patchMinimumSupported = !isset($this->currentUpdate->targetplatform->min_dev_level)
					|| $patchVersion >= $this->currentUpdate->targetplatform->min_dev_level;

				$patchMaximumSupported = !isset($this->currentUpdate->targetplatform->max_dev_level)
					|| $patchVersion <= $this->currentUpdate->targetplatform->max_dev_level;

				if (isset($this->currentUpdate->targetplatform->name)
					&& $product == $this->currentUpdate->targetplatform->name
					&& preg_match('/^' . $this->currentUpdate->targetplatform->version . '/', $this->get('jversion.full', JVERSION))
					&& $patchMinimumSupported
					&& $patchMaximumSupported)
				{
					$phpMatch = false;

					// Check if PHP version supported via <php_minimum> tag, assume true if tag isn't present
					if (!isset($this->currentUpdate->php_minimum) || version_compare(PHP_VERSION, $this->currentUpdate->php_minimum->_data, '>='))
					{
						$phpMatch = true;
					}

					$dbMatch = false;

					// Check if DB & version is supported via <supported_databases> tag, assume supported if tag isn't present
					if (isset($this->currentUpdate->supported_databases))
					{
						$db           = Factory::getDbo();
						$dbType       = strtolower($db->getServerType());
						$dbVersion    = $db->getVersion();
						$supportedDbs = $this->currentUpdate->supported_databases;

						// Do we have a entry for the database?
						if (isset($supportedDbs->$dbType))
						{
							$minumumVersion = $supportedDbs->$dbType;
							$dbMatch        = version_compare($dbVersion, $minumumVersion, '>=');
						}
					}
					else
					{
						// Set to true if the <supported_databases> tag is not set
						$dbMatch = true;
					}

					// Check minimum stability
					$stabilityMatch = true;

					if (isset($this->currentUpdate->stability) && ($this->currentUpdate->stability < $this->minimum_stability))
					{
						$stabilityMatch = false;
					}

					if ($phpMatch && $stabilityMatch && $dbMatch)
					{
						if (isset($this->latest))
						{
							if (version_compare($this->currentUpdate->version->_data, $this->latest->version->_data, '>') == 1)
							{
								$this->latest = $this->currentUpdate;
							}
						}
						else
						{
							$this->latest = $this->currentUpdate;
						}
					}
					else
					{
						$this->latest = new \stdClass;
						$this->latest->php_minimum = $this->currentUpdate->php_minimum;
					}
				}
				break;
			case 'UPDATES':
				// If the latest item is set then we transfer it to where we want to
				if (isset($this->latest))
				{
					foreach (get_object_vars($this->latest) as $key => $val)
					{
						$this->$key = $val;
					}

					unset($this->latest);
					unset($this->currentUpdate);
				}
				elseif (isset($this->currentUpdate))
				{
					// The update might be for an older version of j!
					unset($this->currentUpdate);
				}
				break;
		}
	}

	/**
	 * Character Parser Function
	 *
	 * @param   object  $parser  Parser object.
	 * @param   object  $data    The data.
	 *
	 * @return  void
	 *
	 * @note    This is public because its called externally.
	 * @since   1.7.0
	 */
	public function _characterData($parser, $data)
	{
		$tag = $this->_getLastTag();

		// Throw the data for this item together
		$tag = strtolower($tag);

		if ($tag == 'tag')
		{
			$this->currentUpdate->stability = $this->stabilityTagToInteger((string) $data);

			return;
		}

		if ($tag == 'downloadsource')
		{
			// Grab the last source so we can append the URL
			$source = end($this->downloadSources);
			$source->url = $data;

			return;
		}

		if (isset($this->currentUpdate->$tag))
		{
			$this->currentUpdate->$tag->_data .= $data;
		}
	}

	/**
	 * Loads an XML file from a URL.
	 *
	 * @param   string  $url                The URL.
	 * @param   int     $minimum_stability  The minimum stability required for updating the extension {@see Updater}
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function loadFromXml($url)
	{
		$version    = new Version;
		$httpOption = new Registry;
		$httpOption->set('userAgent', $version->getUserAgent('Joomla', true, false));

		try
		{
			$http     = HttpFactory::getHttp($httpOption);
			$response = $http->get($url);
		}
		catch (\RuntimeException $e)
		{
			$response = null;
		}

		if ($response === null || $response->code !== 200)
		{
			// TODO: Add a 'mark bad' setting here somehow
			Log::add(Text::sprintf('JLIB_UPDATER_ERROR_EXTENSION_OPEN_URL', $url), Log::WARNING, 'jerror');

			return false;
		}


		$this->xmlParser = xml_parser_create('');
		xml_set_object($this->xmlParser, $this);
		xml_set_element_handler($this->xmlParser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->xmlParser, '_characterData');

		if (!xml_parse($this->xmlParser, $response->body))
		{
			Log::add(
				sprintf(
					'XML error: %s at line %d', xml_error_string(xml_get_error_code($this->xmlParser)),
					xml_get_current_line_number($this->xmlParser)
				),
				Log::WARNING, 'updater'
			);

			return false;
		}

		xml_parser_free($this->xmlParser);

		return true;
	}
}
