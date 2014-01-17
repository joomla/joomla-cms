<?php
/**
 * @package     Joomla.Platform
 * @subpackage  MediaWiki
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * MediaWiki API Images class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  MediaWiki
 * @since       12.3
 */
class JMediawikiImages extends JMediawikiObject
{

	/**
     * Method to get all images contained on the given page(s).
     *
     * @param   array    $titles         Page titles to retrieve images.
     * @param   integer  $imagelimit     How many images to return.
     * @param   boolean  $imagecontinue  When more results are available, use this to continue.
     * @param   integer  $imimages       Only list these images.
     * @param   string   $imdir          The direction in which to list.
     *
     * @return  object
     *
     * @since   12.3
     */
	public function getImages(array $titles, $imagelimit = null, $imagecontinue = null, $imimages = null, $imdir = null)
	{
		// Build the request.
		$path = '?action=query&prop=images';

		// Append titles to the request.
		$path .= '&titles=' . $this->buildParameter($titles);

		if (isset($imagelimit))
		{
			$path .= '&imagelimit=' . $imagelimit;
		}

		if ($imagecontinue)
		{
			$path .= '&imagecontinue=';
		}

		if (isset($imimages))
		{
			$path .= '&imimages=' . $imimages;
		}

		if (isset($imdir))
		{
			$path .= '&imdir=' . $imdir;
		}

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}

	/**
     * Method to get all images contained on the given page(s).
     *
     * @param   array  $titles  Page titles to retrieve links.
     *
     * @return  object
     *
     * @since   12.3
     */
	public function getImagesUsed(array $titles)
	{
		// Build the request.
		$path = '?action=query&generator=images&prop=info';

		// Append titles to the request.
		$path .= '&titles=' . $this->buildParameter($titles);

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}

	/**
     * Method to get all image information and upload history.
     *
     * @param   array    $liprop             What image information to get.
     * @param   integer  $lilimit            How many image revisions to return.
     * @param   string   $listart            Timestamp to start listing from.
     * @param   string   $liend              Timestamp to stop listing at.
     * @param   integer  $liurlwidth         URL to an image scaled to this width will be returned..
     * @param   integer  $liurlheight        URL to an image scaled to this height will be returned.
     * @param   string   $limetadataversion  Version of metadata to use.
     * @param   string   $liurlparam         A handler specific parameter string.
     * @param   boolean  $licontinue         When more results are available, use this to continue.
     *
     * @return  object
     *
     * @since   12.3
     */
	public function getImageInfo(array $liprop = null, $lilimit = null, $listart = null, $liend = null, $liurlwidth = null,
		$liurlheight = null, $limetadataversion = null, $liurlparam = null, $licontinue = null)
	{
		// Build the request.
		$path = '?action=query&prop=imageinfo';

		if (isset($liprop))
		{
			$path .= '&liprop=' . $this->buildParameter($liprop);
		}

		if (isset($lilimit))
		{
			$path .= '&lilimit=' . $lilimit;
		}

		if (isset($listart))
		{
			$path .= '&listart=' . $listart;
		}

		if (isset($liend))
		{
			$path .= '&liend=' . $liend;
		}

		if (isset($liurlwidth))
		{
			$path .= '&liurlwidth=' . $liurlwidth;
		}

		if (isset($liurlheight))
		{
			$path .= '&liurlheight=' . $liurlheight;
		}

		if (isset($limetadataversion))
		{
			$path .= '&limetadataversion=' . $limetadataversion;
		}

		if (isset($liurlparam))
		{
			$path .= '&liurlparam=' . $liurlparam;
		}

		if ($licontinue)
		{
			$path .= '&alcontinue=';
		}

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}

	/**
     * Method to enumerate all images.
     *
     * @param   string   $aifrom        The image title to start enumerating from.
     * @param   string   $aito          The image title to stop enumerating at.
     * @param   string   $aiprefix      Search for all image titles that begin with this value.
     * @param   integer  $aiminsize     Limit to images with at least this many bytes.
     * @param   integer  $aimaxsize     Limit to images with at most this many bytes.
     * @param   integer  $ailimit       How many images in total to return.
     * @param   string   $aidir         The direction in which to list.
     * @param   string   $aisha1        SHA1 hash of image.
     * @param   string   $aisha1base36  SHA1 hash of image in base 36.
     * @param   array    $aiprop        What image information to get.
     * @param   string   $aimime        What MIME type to search for.
     *
     * @return  object
     *
     * @since   12.3
     */
	public function enumerateImages($aifrom = null, $aito = null, $aiprefix = null, $aiminsize = null, $aimaxsize = null, $ailimit = null,
		$aidir = null, $aisha1 = null, $aisha1base36 = null, array $aiprop = null, $aimime = null)
	{
		// Build the request.
		$path = '?action=query&list=allimages';

		if (isset($aifrom))
		{
			$path .= '&aifrom=' . $aifrom;
		}

		if (isset($aito))
		{
			$path .= '&aito=' . $aito;
		}

		if (isset($aiprefix))
		{
			$path .= '&aiprefix=' . $aiprefix;
		}

		if (isset($aiminsize))
		{
			$path .= '&aiminsize=' . $aiminsize;
		}

		if (isset($aimaxsize))
		{
			$path .= '&aimaxsize=' . $aimaxsize;
		}

		if (isset($ailimit))
		{
			$path .= '&ailimit=' . $ailimit;
		}

		if (isset($aidir))
		{
			$path .= '&aidir=' . $aidir;
		}
		if (isset($aisha1))
		{
			$path .= '&aisha1=' . $aisha1;
		}

		if (isset($aisha1base36))
		{
			$path .= '&$aisha1base36=' . $aisha1base36;
		}

		if (isset($aiprop))
		{
			$path .= '&aiprop=' . $this->buildParameter($aiprop);
		}

		if (isset($aimime))
		{
			$path .= '&aimime=' . $aimime;
		}

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}
}
