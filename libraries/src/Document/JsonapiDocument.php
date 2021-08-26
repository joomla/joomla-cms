<?php
/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Document;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\ElementInterface;

/**
 * JsonapiDocument class, provides an easy interface to parse output in JSON-API format.
 *
 * @link   http://www.jsonapi.org/
 * @since  4.0.0
 */
class JsonapiDocument extends JsonDocument implements \JsonSerializable
{
	/**
	 * The JsonApi Document object.
	 *
	 * @var    Document
	 * @since  4.0.0
	 */
	protected $document;

	/**
	 * Class constructor.
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since  4.0.0
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set mime type to JSON-API
		$this->_mime = 'application/vnd.api+json';
		$this->_type = 'jsonapi';

		if (\array_key_exists('api_document', $options) && $options['api_document'] instanceof Document)
		{
			$this->document = $options['api_document'];
		}
		else
		{
			$this->document = new Document;
		}
	}

	/**
	 * Set the data object.
	 *
	 * @param   ElementInterface  $element  Element interface.
	 *
	 * @return  $this
	 *
	 * @since  4.0.0
	 */
	public function setData(ElementInterface $element)
	{
		$this->document->setData($element);

		return $this;
	}

	/**
	 * Set the errors array.
	 *
	 * @param   array  $errors  Error array.
	 *
	 * @return   $this
	 *
	 * @since  4.0.0
	 */
	public function setErrors($errors)
	{
		$this->document->setErrors($errors);

		return $this;
	}

	/**
	 * Set the JSON-API array.
	 *
	 * @param   array  $jsonapi  JSON-API array.
	 *
	 * @return   $this
	 *
	 * @since  4.0.0
	 */
	public function setJsonapi($jsonapi)
	{
		$this->document->setJsonapi($jsonapi);

		return $this;
	}

	/**
	 * Map everything to arrays.
	 *
	 * @return array
	 *
	 * @since  4.0.0
	 */
	public function toArray()
	{
		return $this->document->toArray();
	}

	/**
	 * Map to string.
	 *
	 * @return string
	 *
	 * @since  4.0.0
	 */
	public function __toString()
	{
		return json_encode($this->toArray());
	}

	/**
	 * Outputs the document.
	 *
	 * @param   boolean  $cache   If true, cache the output.
	 * @param   array    $params  Associative array of attributes.
	 *
	 * @return  string  The rendered data.
	 *
	 * @since  4.0.0
	 */
	public function render($cache = false, $params = array())
	{
		$app = Factory::getApplication();

		if ($mdate = $this->getModifiedDate())
		{
			$app->modifiedDate = $mdate;
		}

		$app->mimeType = $this->_mime;
		$app->charSet  = $this->_charset;

		return json_encode($this->document);
	}

	/**
	 * Serialize for JSON usage.
	 *
	 * @return array
	 *
	 * @since  4.0.0
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Add a link to the output.
	 *
	 * @param   string  $key    The name of the link
	 * @param   string  $value  The link
	 *
	 * @return  $this
	 *
	 * @since  4.0.0
	 */
	public function addLink($key, $value)
	{
		$this->document->addLink($key, $value);

		return $this;
	}

	/**
	 * Add a link to the output.
	 *
	 * @param   string  $key    The name of the metadata key
	 * @param   string  $value  The value
	 *
	 * @return  $this
	 *
	 * @since  4.0.0
	 */
	public function addMeta($key, $value)
	{
		$this->document->addMeta($key, $value);

		return $this;
	}
}
