<?php
/**
 * @package     Joomla.Platform
 * @subpackage  MediaWiki
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * MediaWiki API Categories class for the Joomla Platform.
 *
 * @since  3.1.4
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
	 * @since   3.0.0
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
	 * @since   3.1.4
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
	 * @since   3.1.4
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
	 * Method to get information about the pages within a category
	 *
	 * @param   string  $cmtitle               The category title, must contain 'Category:' prefix, cannot be used together with $cmpageid
	 * @param   string  $cmpageid              The category's page ID, cannot be used together with $cmtitle
	 * @param   string  $cmlimit               Maximum number of pages to retrieve
	 * @param   array   $cmprop                Array of properties to retrieve
	 * @param   array   $cmnamespace           Namespaces to retrieve pages from
	 * @param   array   $cmtype                Array of category members to include, ignored if $cmsort is set to 'timestamp'
	 * @param   string  $cmstart               Timestamp to start listing from, only used if $cmsort is set to 'timestamp'
	 * @param   string  $cmend                 Timestamp to end listing at, only used if $cmsort is set to 'timestamp'
	 * @param   string  $cmstartsortkey        Hexadecimal key to start listing from, only used if $cmsort is set to 'sortkey'
	 * @param   string  $cmendsortkey          Hexadecimal key to end listing at, only used if $cmsort is set to 'sortkey'
	 * @param   string  $cmstartsortkeyprefix  Hexadecimal key prefix to start listing from, only used if $cmsort is set to 'sortkey',
	 *                                         overrides $cmstartsortkey
	 * @param   string  $cmendsortkeyprefix    Hexadecimal key prefix to end listing before, only used if $cmsort is set to 'sortkey',
	 *                                         overrides $cmendsortkey
	 * @param   string  $cmsort                Property to sort by
	 * @param   string  $cmdir                 Direction to sort in
	 * @param   string  $cmcontinue            Used to continue a previous request
	 *
	 * @return  object
	 *
	 * @since   3.2.2 (CMS)
	 * @throws  RuntimeException
	 */
	public function getCategoryMembers($cmtitle = null, $cmpageid = null, $cmlimit = null, array $cmprop = null, array $cmnamespace = null,
		array $cmtype = null, $cmstart = null, $cmend = null, $cmstartsortkey = null, $cmendsortkey = null, $cmstartsortkeyprefix = null,
		$cmendsortkeyprefix = null, $cmsort = null, $cmdir = null, $cmcontinue = null)
	{
		// Build the request.
		$path = '?action=query&list=categorymembers';

		// Make sure both $cmtitle and $cmpageid are not set
		if (isset($cmtitle) && isset($cmpageid))
		{
			throw new RuntimeException('Both the $cmtitle and $cmpageid parameters cannot be set, please only use one of the two.');
		}

		if (isset($cmtitle))
		{
			// Verify that the Category: prefix exists
			if (strpos($cmtitle, 'Category:') !== 0)
			{
				throw new RuntimeException('The $cmtitle parameter must include the Category: prefix.');
			}

			$path .= '&cmtitle=' . $cmtitle;
		}

		if (isset($cmpageid))
		{
			$path .= '&cmpageid=' . $cmpageid;
		}

		if (isset($cmlimit))
		{
			$path .= '&cmlimit=' . $cmlimit;
		}

		if (isset($cmprop))
		{
			$path .= '&cmprop=' . $this->buildParameter($cmprop);
		}

		if (isset($cmnamespace))
		{
			$path .= '&cmnamespace=' . $this->buildParameter($cmnamespace);
		}

		if (isset($cmtype))
		{
			$path .= '&cmtype=' . $this->buildParameter($cmtype);
		}

		if (isset($cmstart))
		{
			$path .= '&cmstart=' . $cmstart;
		}

		if (isset($cmend))
		{
			$path .= '&cmend=' . $cmend;
		}

		if (isset($cmstartsortkey))
		{
			$path .= '&cmstartsortkey=' . $cmstartsortkey;
		}

		if (isset($cmendsortkey))
		{
			$path .= '&cmendsortkey=' . $cmendsortkey;
		}

		if (isset($cmstartsortkeyprefix))
		{
			$path .= '&cmstartsortkeyprefix=' . $cmstartsortkeyprefix;
		}

		if (isset($cmendsortkeyprefix))
		{
			$path .= '&cmendsortkeyprefix=' . $cmendsortkeyprefix;
		}

		if (isset($cmsort))
		{
			$path .= '&cmsort=' . $cmsort;
		}

		if (isset($cmdir))
		{
			$path .= '&cmdir=' . $cmdir;
		}

		if (isset($cmcontinue))
		{
			$path .= '&cmcontinue=' . $cmcontinue;
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
	 * @since   3.1.4
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
	 * @since   3.1.4
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
