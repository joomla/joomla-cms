<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Google Picasa data class for the Joomla Platform.
 *
 * @since       12.3
 * @deprecated  4.0  Use the `joomla/google` package via Composer instead
 */
class JGoogleDataPicasaPhoto extends JGoogleData
{
	/**
	 * @var    SimpleXMLElement  The photo's XML
	 * @since  12.3
	 */
	protected $xml;

	/**
	 * Constructor.
	 *
	 * @param   SimpleXMLElement  $xml      XML from Google
	 * @param   Registry          $options  Google options object
	 * @param   JGoogleAuth       $auth     Google data http client object
	 *
	 * @since   12.3
	 */
	public function __construct(SimpleXMLElement $xml, Registry $options = null, JGoogleAuth $auth = null)
	{
		$this->xml = $xml;

		parent::__construct($options, $auth);

		if (isset($this->auth) && !$this->auth->getOption('scope'))
		{
			$this->auth->setOption('scope', 'https://picasaweb.google.com/data/');
		}
	}

	/**
	 * Method to delete a Picasa photo
	 *
	 * @param   mixed  $match  Check for most up to date photo
	 *
	 * @return  boolean  Success or failure.
	 *
	 * @since   12.3
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function delete($match = '*')
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getLink();

			if ($match === true)
			{
				$match = $this->xml->xpath('./@gd:etag');
				$match = $match[0];
			}

			try
			{
				$jdata = $this->query($url, null, array('GData-Version' => 2, 'If-Match' => $match), 'delete');
			}
			catch (Exception $e)
			{
				if (strpos($e->getMessage(), 'Error code 412 received requesting data: Mismatch: etags') === 0)
				{
					throw new RuntimeException("Etag match failed: `$match`.", $e->getCode(), $e);
				}

				throw $e;
			}

			if ($jdata->body != '')
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}

			$this->xml = null;

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get the photo link
	 *
	 * @param   string  $type  Type of link to return
	 *
	 * @return  string  Link or false on failure
	 *
	 * @since   12.3
	 */
	public function getLink($type = 'edit')
	{
		$links = $this->xml->link;

		foreach ($links as $link)
		{
			if ($link->attributes()->rel == $type)
			{
				return (string) $link->attributes()->href;
			}
		}

		return false;
	}

	/**
	 * Method to get the photo's URL
	 *
	 * @return  string  Link
	 *
	 * @since   12.3
	 */
	public function getUrl()
	{
		return (string) $this->xml->children()->content->attributes()->src;
	}

	/**
	 * Method to get the photo's thumbnails
	 *
	 * @return  array  An array of thumbnails
	 *
	 * @since   12.3
	 */
	public function getThumbnails()
	{
		$thumbs = array();

		foreach ($this->xml->children('media', true)->group->thumbnail as $item)
		{
			$url = (string) $item->attributes()->url;
			$width = (int) $item->attributes()->width;
			$height = (int) $item->attributes()->height;
			$thumbs[$width] = array('url' => $url, 'w' => $width, 'h' => $height);
		}

		return $thumbs;
	}

	/**
	 * Method to get the title of the photo
	 *
	 * @return  string  Photo title
	 *
	 * @since   12.3
	 */
	public function getTitle()
	{
		return (string) $this->xml->children()->title;
	}

	/**
	 * Method to get the summary of the photo
	 *
	 * @return  string  Photo description
	 *
	 * @since   12.3
	 */
	public function getSummary()
	{
		return (string) $this->xml->children()->summary;
	}

	/**
	 * Method to get the access level of the photo
	 *
	 * @return  string  Photo access level
	 *
	 * @since   12.3
	 */
	public function getAccess()
	{
		return (string) $this->xml->children('gphoto', true)->access;
	}

	/**
	 * Method to get the time of the photo
	 *
	 * @return  double  Photo time
	 *
	 * @since   12.3
	 */
	public function getTime()
	{
		return (double) $this->xml->children('gphoto', true)->timestamp / 1000;
	}

	/**
	 * Method to get the size of the photo
	 *
	 * @return  int  Photo size
	 *
	 * @since   12.3
	 */
	public function getSize()
	{
		return (int) $this->xml->children('gphoto', true)->size;
	}

	/**
	 * Method to get the height of the photo
	 *
	 * @return  int  Photo height
	 *
	 * @since   12.3
	 */
	public function getHeight()
	{
		return (int) $this->xml->children('gphoto', true)->height;
	}

	/**
	 * Method to get the width of the photo
	 *
	 * @return  int  Photo width
	 *
	 * @since   12.3
	 */
	public function getWidth()
	{
		return (int) $this->xml->children('gphoto', true)->width;
	}

	/**
	 * Method to set the title of the photo
	 *
	 * @param   string  $title  New photo title
	 *
	 * @return  JGoogleDataPicasaPhoto  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setTitle($title)
	{
		$this->xml->children()->title = $title;

		return $this;
	}

	/**
	 * Method to set the summary of the photo
	 *
	 * @param   string  $summary  New photo description
	 *
	 * @return  JGoogleDataPicasaPhoto  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setSummary($summary)
	{
		$this->xml->children()->summary = $summary;

		return $this;
	}

	/**
	 * Method to set the access level of the photo
	 *
	 * @param   string  $access  New photo access level
	 *
	 * @return  JGoogleDataPicasaPhoto  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setAccess($access)
	{
		$this->xml->children('gphoto', true)->access = $access;

		return $this;
	}

	/**
	 * Method to set the time of the photo
	 *
	 * @param   int  $time  New photo time
	 *
	 * @return  JGoogleDataPicasaPhoto  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setTime($time)
	{
		$this->xml->children('gphoto', true)->timestamp = $time * 1000;

		return $this;
	}

	/**
	 * Method to modify a Picasa Photo
	 *
	 * @param   string  $match  Optional eTag matching parameter
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 */
	public function save($match = '*')
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getLink();

			if ($match === true)
			{
				$match = $this->xml->xpath('./@gd:etag');
				$match = $match[0];
			}

			try
			{
				$headers = array('GData-Version' => 2, 'Content-type' => 'application/atom+xml', 'If-Match' => $match);
				$jdata = $this->query($url, $this->xml->asXml(), $headers, 'put');
			}
			catch (Exception $e)
			{
				if (strpos($e->getMessage(), 'Error code 412 received requesting data: Mismatch: etags') === 0)
				{
					throw new RuntimeException("Etag match failed: `$match`.", $e->getCode(), $e);
				}

				throw $e;
			}

			$this->xml = $this->safeXml($jdata->body);

			return $this;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Refresh photo data
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 */
	public function refresh()
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getLink();
			$jdata = $this->query($url, null, array('GData-Version' => 2));
			$this->xml = $this->safeXml($jdata->body);

			return $this;
		}
		else
		{
			return false;
		}
	}
}
