<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google Picasa data class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
class JGoogleDataPicasaPhoto extends JGoogleData
{
	/**
	 * @var    SimpleXMLElement  The photo's XML
	 * @since  1234
	 */
	protected $xml;

	/**
	 * Constructor.
	 *
	 * @param   SimpleXMLElement  $xml      XML from Google
	 * @param   JRegistry         $options  Google options object
	 * @param   JGoogleAuth       $auth     Google data http client object
	 *
	 * @since   1234
	 */
	public function __construct(SimpleXMLElement $xml, JRegistry $options = null, JGoogleAuth $auth = null)
	{
		$this->xml = $xml;

		$options = isset($options) ? $options : new JRegistry;
		if (!$options->get('scope'))
		{
			$options->set('scope', 'https://picasaweb.google.com/data/');
		}
		if (isset($auth) && !$auth->getOption('scope'))
		{
			$auth->setOption('scope', 'https://picasaweb.google.com/data/');
		}

		parent::__construct($options, $auth);
	}

	/**
	 * Method to delete a Picasa photo
	 *
	 * @param   mixed  $match  Check for most up to date photo
	 *
	 * @return  bool  Success or failure.
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function delete($match = '*')
	{
		if ($this->authenticated())
		{
			$url = $this->getLink();
			if ($match === true)
			{
				$match = $this->xml->xpath('./@gd:etag');
				$match = $match[0];
			}

			try
			{
				$jdata = $this->auth->query($url, null, array('GData-Version' => 2, 'If-Match' => $match), 'delete');
			}
			catch (Exception $e)
			{
				if ($jdata->code == 412)
				{
					throw new RuntimeException("Etag match failed: `$match`.");
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
	 * @since   1234
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
	 * @since   1234
	 */
	public function getURL()
	{
		return (string) $this->xml->children()->content->attributes()->src;
	}

	/**
	 * Method to get the photo's thumbnails
	 *
	 * @return  array  An array of thumbnails
	 *
	 * @since   1234
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
	 * @since   1234
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
	 * @since   1234
	 */
	public function getSummary()
	{
		return (string) $this->xml->children('gphoto', true)->access;
	}

	/**
	 * Method to get the access level of the photo
	 *
	 * @return  string  Photo access level
	 *
	 * @since   1234
	 */
	public function getAccess()
	{
		return (string) $this->xml->children('gphoto', true)->access;
	}

	/**
	 * Method to get the time of the photo
	 *
	 * @return  int  Photo time
	 *
	 * @since   1234
	 */
	public function getTime()
	{
		return (int) $this->xml->children('gphoto', true)->timestamp;
	}

	/**
	 * Method to get the size of the photo
	 *
	 * @return  int  Photo size
	 *
	 * @since   1234
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
	 * @since   1234
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
	 * @since   1234
	 */
	public function getWidth()
	{
		return (int) $this->xml->children('gphoto', true)->width;
	}

	/**
	 * Method to get the time of the photo
	 *
	 * @return  int  Photo time
	 *
	 * @since   1234
	 */
	public function getTime()
	{
		return (int) $this->xml->children('gphoto', true)->timestamp;
		return $this;
	}

	/**
	 * Method to set the title of the photo
	 *
	 * @param   string  $title  New photo title
	 *
	 * @return  JGoogleDataPicasaPhoto  The object for method chaining
	 *
	 * @since   1234
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
	 * @since   1234
	 */
	public function setSummary($summary)
	{
		$this->xml->children()->summary = $summary;
		return $this;
	}

	/**
	 * Method to set the time of the photo
	 *
	 * @param   int  $time  New photo time
	 *
	 * @return  JGoogleDataPicasaPhoto  The object for method chaining
	 *
	 * @since   1234
	 */
	public function setTime($time)
	{
		$this->xml->children('gphoto', true)->timestamp = $time;
		return $this;
	}

	/**
	 * Method to modify a Picasa Photo
	 *
	 * @param   string  $match  Optional eTag matching parameter
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 */
	public function save($match = '*')
	{
		if ($this->authenticated())
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
				$jdata = $this->auth->query($url, $this->xml->asXML(), $headers, 'put');
			}
			catch (Exception $e)
			{
				if (strpos($e->getMessage(), 'Error code 412 received requesting data: Mismatch: etags') === 0)
				{
					throw new RuntimeException("Etag match failed: `$match`.");
				}
				throw $e;
			}

			$this->xml = $this->safeXML($jdata->body);
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
	 * @since   1234
	 */
	public function refresh()
	{
		if ($this->authenticated())
		{
			$url = $this->getLink();
			$jdata = $this->auth->query($url, null, array('GData-Version' => 2));
			$this->xml = $this->safeXML($jdata->body);
			return $this;
		}
		else
		{
			return false;
		}
	}
}
