<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved
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
class JGoogleDataPicasaAlbum extends JGoogleData
{
	/**
	 * @var    SimpleXMLElement  The album's XML
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
	 * Method to delete a Picasa album
	 *
	 * @param   mixed  $match  Check for most up to date album
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
	 * Method to get the album link
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
	 * Method to get the title of the album
	 *
	 * @return  string  Album title
	 *
	 * @since   12.3
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
	 * @since   12.3
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
	 * @since   12.3
	 */
	public function getLocation()
	{
		return (string) $this->xml->children('gphoto', true)->location;
	}

	/**
	 * Method to get the access level of the album
	 *
	 * @return  string  Album access level
	 *
	 * @since   12.3
	 */
	public function getAccess()
	{
		return (string) $this->xml->children('gphoto', true)->access;
	}

	/**
	 * Method to get the time of the album
	 *
	 * @return  double  Album time
	 *
	 * @since   12.3
	 */
	public function getTime()
	{
		return (double) $this->xml->children('gphoto', true)->timestamp / 1000;
	}

	/**
	 * Method to set the title of the album
	 *
	 * @param   string  $title  New album title
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setTitle($title)
	{
		$this->xml->children()->title = $title;

		return $this;
	}

	/**
	 * Method to set the summary of the album
	 *
	 * @param   string  $summary  New album summary
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setSummary($summary)
	{
		$this->xml->children()->summary = $summary;

		return $this;
	}

	/**
	 * Method to set the location of the album
	 *
	 * @param   string  $location  New album location
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setLocation($location)
	{
		$this->xml->children('gphoto', true)->location = $location;

		return $this;
	}

	/**
	 * Method to set the access level of the album
	 *
	 * @param   string  $access  New album access
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setAccess($access)
	{
		$this->xml->children('gphoto', true)->access = $access;

		return $this;
	}

	/**
	 * Method to set the time of the album
	 *
	 * @param   int  $time  New album time
	 *
	 * @return  JGoogleDataPicasaAlbum  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setTime($time)
	{
		$this->xml->children('gphoto', true)->timestamp = $time * 1000;

		return $this;
	}

	/**
	 * Method to modify a Picasa Album
	 *
	 * @param   string  $match  Optional eTag matching parameter
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws  Exception
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
	 * Refresh Picasa Album
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
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

	/**
	 * Method to retrieve a list of Picasa Photos
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listPhotos()
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getLink('http://schemas.google.com/g/2005#feed');
			$jdata = $this->query($url, null, array('GData-Version' => 2));
			$xml = $this->safeXml($jdata->body);

			if (isset($xml->children()->entry))
			{
				$items = array();

				foreach ($xml->children()->entry as $item)
				{
					$items[] = new JGoogleDataPicasaPhoto($item, $this->options, $this->auth);
				}

				return $items;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Add photo
	 *
	 * @param   string  $file     Path of file to upload
	 * @param   string  $title    Title to give to file (defaults to filename)
	 * @param   string  $summary  Description of the file
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function upload($file, $title = '', $summary = '')
	{
		if ($this->isAuthenticated())
		{
			jimport('joomla.filesystem.file');
			$title = $title != '' ? $title : JFile::getName($file);

			if (!($type = $this->getMime($file)))
			{
				throw new RuntimeException('Inappropriate file type.');
			}

			if (!($data = file_get_contents($file)))
			{
				throw new RuntimeException("Cannot access file: `$file`");
			}

			$xml = new SimpleXMLElement('<entry></entry>');
			$xml->addAttribute('xmlns', 'http://www.w3.org/2005/Atom');
			$xml->addChild('title', $title);
			$xml->addChild('summary', $summary);
			$cat = $xml->addChild('category', '');
			$cat->addAttribute('scheme', 'http://schemas.google.com/g/2005#kind');
			$cat->addAttribute('term', 'http://schemas.google.com/photos/2007#photo');

			$post = "Media multipart posting\n";
			$post .= "--END_OF_PART\n";
			$post .= "Content-Type: application/atom+xml\n\n";
			$post .= $xml->asXml() . "\n";
			$post .= "--END_OF_PART\n";
			$post .= "Content-Type: {$type}\n\n";
			$post .= $data;

			$jdata = $this->query($this->getLink(), $post, array('GData-Version' => 2, 'Content-Type: multipart/related'), 'post');

			return new JGoogleDataPicasaPhoto($this->safeXml($jdata->body), $this->options, $this->auth);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Add photo
	 *
	 * @param   string  $file  Filename
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	protected function getMime($file)
	{
		switch (strtolower(JFile::getExt($file)))
		{
			case 'bmp':
			case 'bm':
			return 'image/bmp';
			case 'gif':
			return 'image/gif';
			case 'jpg':
			case 'jpeg':
			case 'jpe':
			case 'jif':
			case 'jfif':
			case 'jfi':
			return 'image/jpeg';
			case 'png':
			return 'image/png';
			case '3gp':
			return 'video/3gpp';
			case 'avi':
			return 'video/avi';
			case 'mov':
			case 'moov':
			case 'qt':
			return 'video/quicktime';
			case 'mp4':
			case 'm4a':
			case 'm4p':
			case 'm4b':
			case 'm4r':
			case 'm4v':
			return 'video/mp4';
			case 'mpg':
			case 'mpeg':
			case 'mp1':
			case 'mp2':
			case 'mp3':
			case 'm1v':
			case 'm1a':
			case 'm2a':
			case 'mpa':
			case 'mpv':
			return 'video/mpeg';
			case 'asf':
			return 'video/x-ms-asf';
			case 'wmv':
			return 'video/x-ms-wmv';
			default:
			return false;
		}
	}
}
