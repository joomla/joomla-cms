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
 * MediaWiki API Categories class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  MediaWiki
 * @since       12.3
 */
class JMediawikiCategories extends JMediawikiObject
{
	/**
     * Method to list all categories the page(s) belong to.
     *
     * @param   array    $titles        Page titles to retrieve categories.
     * @param   array    $clprop        List of additional properties to get.
     * @param   array    $clshow        Type of categories to show.
     * @param   integer  $cllimit       Number of categories to return.
     * @param   boolean  $clcontinue    Continue when more results are available.
     * @param   array    $clcategories  Only list these categories.
     * @param   string   $cldir         Direction of listing.
     *
     * @return  object
     *
     * @since   12.1
     */
	public function getCategories(array $titles, array $clprop = null, array $clshow = null, $cllimit = null, $clcontinue = false,
		array $clcategories = null, $cldir = null)
	{
		// Build the request.
		$path = '?action=query&prop=categories';

		// Append titles to the request.
		$path .= '&titles=' . $this->buildParameter($titles);

		if (isset($clprop))
		{
			$path .= '&clprop=' . $this->buildParameter($clprop);
		}

		if (isset($clshow))
		{
			$path .= '&$clshow=' . $this->buildParameter($clshow);
		}

		if (isset($cllimit))
		{
			$path .= '&cllimit=' . $cllimit;
		}

		if ($clcontinue)
		{
			$path .= '&clcontinue=';
		}

		if (isset($clcategories))
		{
			$path .= '&clcategories=' . $this->buildParameter($clcategories);
		}

		if (isset($cldir))
		{
			$path .= '&cldir=' . $cldir;
		}

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}

	/**
     * Method to get information about all categories used.
     *
     * @param   array  $titles  Page titles to retrieve categories.
     *
     * @return  object
     *
     * @since   12.3
     */
	public function getCategoriesUsed(array $titles)
	{
		// Build the request
		$path = '?action=query&generator=categories&prop=info';

		// Append titles to the request
		$path .= '&titles=' . $this->buildParameter($titles);

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}

	/**
     * Method to get information about the given categories.
     *
     * @param   array    $titles      Page titles to retrieve categories.
     * @param   boolean  $clcontinue  Continue when more results are available.
     *
     * @return  object
     *
     * @since   12.3
     */
	public function getCategoriesInfo(array $titles, $clcontinue = false)
	{
		// Build the request.
		$path = '?action=query&prop=categoryinfo';

		// Append titles to the request
		$path .= '&titles=' . $this->buildParameter($titles);

		if ($clcontinue)
		{
			$path .= '&clcontinue=';
		}

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}

	/**
     * Method to enumerate all categories.
     *
     * @param   string   $acfrom    The category to start enumerating from.
     * @param   string   $acto      The category to stop enumerating at.
     * @param   string   $acprefix  Search for all category titles that begin with this value.
     * @param   string   $acdir     Direction to sort in.
     * @param   integer  $acmin     Minimum number of category members.
     * @param   integer  $acmax     Maximum number of category members.
     * @param   integer  $aclimit   How many categories to return.
     * @param   array    $acprop    Which properties to get.
     *
     * @return  object
     *
     * @since   12.3
     */
	public function enumerateCategories($acfrom = null, $acto = null, $acprefix = null, $acdir = null, $acmin = null,
		$acmax = null, $aclimit = null, array $acprop = null)
	{
		// Build the request.
		$path = '?action=query&list=allcategories';

		if (isset($acfrom))
		{
			$path .= '&acfrom=' . $acfrom;
		}

		if (isset($acto))
		{
			$path .= '&acto=' . $acto;
		}

		if (isset($acprefix))
		{
			$path .= '&acprefix=' . $acprefix;
		}

		if (isset($acdir))
		{
			$path .= '&acdir=' . $acdir;
		}

		if (isset($acfrom))
		{
			$path .= '&acfrom=' . $acfrom;
		}

		if (isset($acmin))
		{
			$path .= '&acmin=' . $acmin;
		}

		if (isset($acmax))
		{
			$path .= '&acmax=' . $acmax;
		}

		if (isset($aclimit))
		{
			$path .= '&aclimit=' . $aclimit;
		}

		if (isset($acprop))
		{
			$path .= '&acprop=' . $this->buildParameter($acprop);
		}

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}

	/**
     * Method to list change tags.
     *
     * @param   array   $tgprop   List of properties to get.
     * @param   string  $tglimit  The maximum number of tags to limit.
     *
     * @return  object
     *
     * @since   12.3
     */
	public function getChangeTags(array $tgprop = null, $tglimit = null)
	{
		// Build the request.
		$path = '?action=query&list=tags';

		if (isset($tgprop))
		{
			$path .= '&tgprop=' . $this->buildParameter($tgprop);
		}

		if (isset($tglimit))
		{
			$path .= '&tglimit=' . $tglimit;
		}

		// @TODO add support for $tgcontinue

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $this->validateResponse($response);
	}
}
