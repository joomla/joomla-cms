<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Changelog;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;
use RuntimeException;

/**
 * Changelog class.
 *
 * @since  4.0.0
 */
class Changelog extends CMSObject
{
	/**
	 * Update manifest `<element>` element
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $element;

	/**
	 * Update manifest `<type>` element
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $type;

	/**
	 * Update manifest `<version>` element
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $version;

	/**
	 * Update manifest `<security>` element
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $security = array();

	/**
	 * Update manifest `<fix>` element
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fix = array();

	/**
	 * Update manifest `<language>` element
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $language = array();

	/**
	 * Update manifest `<addition>` element
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $addition = array();

	/**
	 * Update manifest `<change>` elements
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $change = array();

	/**
	 * Update manifest `<remove>` element
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $remove = array();

	/**
	 * Update manifest `<maintainer>` element
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $note = array();

	/**
	 * List of node items
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $items = array();

	/**
	 * Resource handle for the XML Parser
	 *
	 * @var    resource
	 * @since  4.0.0
	 */
	protected $xmlParser;

	/**
	 * Element call stack
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $stack = array('base');

	/**
	 * Object containing the current update data
	 *
	 * @var    \stdClass
	 * @since  4.0.0
	 */
	protected $currentChangelog;

	/**
	 * The version to match the changelog
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	private $matchVersion = '';

	/**
	 * Object containing the latest changelog data
	 *
	 * @var    \stdClass
	 * @since  4.0.0
	 */
	protected $latest;

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	protected function getStackLocation()
	{
		return implode('->', $this->stack);
	}

	/**
	 * Get the last position in stack count
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	protected function getLastTag()
	{
		return $this->stack[\count($this->stack) - 1];
	}

	/**
	 * Set the version to match.
	 *
	 * @param   string  $version  The version to match
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setVersion(string $version)
	{
		$this->matchVersion = $version;
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
	public function startElement($parser, $name, $attrs = array())
	{
		$this->stack[] = $name;
		$tag           = $this->getStackLocation();

		// Reset the data
		if (isset($this->$tag))
		{
			$this->$tag->data = '';
		}

		$name = strtolower($name);

		if (!isset($this->currentChangelog->$name))
		{
			$this->currentChangelog->$name = new \stdClass;
		}

		$this->currentChangelog->$name->data = '';

		foreach ($attrs as $key => $data)
		{
			$key                                 = strtolower($key);
			$this->currentChangelog->$name->$key = $data;
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
	public function endElement($parser, $name)
	{
		array_pop($this->stack);

		switch ($name)
		{
			case 'SECURITY':
			case 'FIX':
			case 'LANGUAGE':
			case 'ADDITION':
			case 'CHANGE':
			case 'REMOVE':
			case 'NOTE':
				$name = strtolower($name);
				$this->currentChangelog->$name->data = $this->items;
				$this->items = array();
				break;
			case 'CHANGELOG':
				if (version_compare($this->currentChangelog->version->data, $this->matchVersion, '==') === true)
				{
					$this->latest = $this->currentChangelog;
				}

				// No version match, empty it
				$this->currentChangelog = new \stdClass;
				break;
			case 'CHANGELOGS':
				// If the latest item is set then we transfer it to where we want to
				if (isset($this->latest))
				{
					foreach (get_object_vars($this->latest) as $key => $val)
					{
						$this->$key = $val;
					}

					unset($this->latest);
					unset($this->currentChangelog);
				}
				elseif (isset($this->currentChangelog))
				{
					// The update might be for an older version of j!
					unset($this->currentChangelog);
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
	public function characterData($parser, $data)
	{
		$tag = $this->getLastTag();

		switch ($tag)
		{
			case 'ITEM':
				$this->items[] = $data;
				break;
			case 'SECURITY':
			case 'FIX':
			case 'LANGUAGE':
			case 'ADDITION':
			case 'CHANGE':
			case 'REMOVE':
			case 'NOTE':
				break;
			default:
				// Throw the data for this item together
				$tag = strtolower($tag);

				if (isset($this->currentChangelog->$tag))
				{
					$this->currentChangelog->$tag->data .= $data;
				}
				break;
		}
	}

	/**
	 * Loads an XML file from a URL.
	 *
	 * @param   string  $url  The URL.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.0.0
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
		catch (RuntimeException $e)
		{
			$response = null;
		}

		if ($response === null || $response->code !== 200)
		{
			// @todo: Add a 'mark bad' setting here somehow
			Log::add(Text::sprintf('JLIB_UPDATER_ERROR_EXTENSION_OPEN_URL', $url), Log::WARNING, 'jerror');

			return false;
		}

		$this->currentChangelog = new \stdClass;

		$this->xmlParser = xml_parser_create('');
		xml_set_object($this->xmlParser, $this);
		xml_set_element_handler($this->xmlParser, 'startElement', 'endElement');
		xml_set_character_data_handler($this->xmlParser, 'characterData');

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
