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
	 * @var    SimpleXMLElement  The album's XML
	 * @since  1234
	 */
	protected $xml;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  Google options object
	 * @param   JGoogleAuth  $auth     Google data http client object
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
	 * Method to delete a Picasa album
	 *
	 * @param   mixed  $match  Check for most up to date album
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

	    if ($jdata->body  != '')
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
	 * Method to get the album link
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
		foreach($links as $link)
		{
			if ($link->attributes()->rel == $type)
			{
				return (string) $link->attributes()->href;
			}
		}
		return false;
	}

	/**
	 * Method to get the title of the album
	 *
	 * @return  string  Album title
	 *
	 * @since   1234
	 */
	public function getTitle()
	{
		return (string) $this->xml->children()->title;
	}

	/**
	 * Method to get the summary of the album
	 *
	 * @return  string  Album summary
	 *
	 * @since   1234
	 */
	public function getSummary()
	{
		return (string) $this->xml->children()->summary;
	}

	/**
	 * Method to get the location of the album
	 *
	 * @return  string  Album location
	 *
	 * @since   1234
	 */
	public function getLocation()
	{
		return (string) $this->xml->children('gphoto')->location;
	}

	/**
	 * Method to get the access level of the album
	 *
	 * @return  string  Album access level
	 *
	 * @since   1234
	 */
	public function getAccess()
	{
		return (string) $this->xml->children('gphoto')->access;
	}

	/**
	 * Method to get the time of the album
	 *
	 * @return  int  Album time
	 *
	 * @since   1234
	 */
	public function getTime()
	{
		return (int) $this->xml->children('gphoto')->timestamp;
		return $this;
	}

	/**
	 * Method to get the title of the album
	 *
	 * @param   string  $title  New album title
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   1234
	 */
	public function setTitle($title)
	{
		$this->xml->children()->title = $title;
		return $this;
	}

	/**
	 * Method to get the summary of the album
	 *
	 * @param   string  $summary  New album summary
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   1234
	 */
	public function setSummary($summary)
	{
		$this->xml->children()->summary = $summary;
		return $this;
	}

	/**
	 * Method to get the location of the album
	 *
	 * @param   string  $location  New album location
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   1234
	 */
	public function setLocation($location)
	{
		$this->xml->children('gphoto')->location = $location;
		return $this;
	}

	/**
	 * Method to get the access level of the album
	 *
	 * @param   string  $access  New album access
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   1234
	 */
	public function setAccess($access)
	{
		$this->xml->children('gphoto')->access = $access;
		return $this;
	}

	/**
	 * Method to get the time of the album
	 *
	 * @param   int  $title  New album time
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   1234
	 */
	public function setTime($time)
	{
		$this->xml->children('gphoto')->timestamp = $time;
		return $this;
	}

	/**
	 * Method to modify a Picasa Album
	 *
	 * @param   string  $url      URL of album to delete
	 * @param   array   $options  Album settings
	 * @param   string  $match    Optional eTag matching parameter
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
		sleep(25);
				$jdata = $this->auth->query($url, $this->xml->asXML(), array('GData-Version' => 2, 'Content-type' => 'application/atom+xml', 'If-Match' => $match), 'put');
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
	 * Get Picasa Album
	 *
	 * @param   string  $url  URL of album to get
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function refresh()
	{
		if ($this->authenticated())
		{
			$url = $this->getLink();
			$jdata = $this->auth->query($url, null, array('GData-Version' => 2));
			$this->xml = $this->safeXML($jdata->body);echo $jdata->body;
			return $this;
		}
		else
		{
			return false;
		}
	}
}
