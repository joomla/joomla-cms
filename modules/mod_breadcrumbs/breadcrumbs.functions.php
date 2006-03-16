<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Get the breadcrumbs string in XHTML format for output to the page
 *
 * @param	array	$items		Pathway items to build a BreadCrumbs string
 * @param	string	$separator	BreadCrumbs separator string [XHTML]
 * @return	string	XHTML Compliant breadcrumbs string
 * @since	1.1
 */
function showBreadCrumbs(& $items, $separator)
{

	/*
	 * Initialize variables
	 */
	$breadcrumbs	= '<span class="breadcrumbs pathway">';
	$i						= null;
	$numItems		= count($items);

	for ($i = 0; $i < $numItems; $i ++)
	{
		$items[$i]->name = stripslashes(ampReplace($items[$i]->name));

		// If a link is present create an html link, if not just use the name
		if (empty ($items[$i]->link) || $numItems == $i +1)
		{
			$link = $items[$i]->name;
		}
		else
		{
			$link = '<a href="'.sefRelToAbs($items[$i]->link).'" class="pathway">'.$items[$i]->name.'</a>';
		}

		$link = ampReplace($link);

		// Add the link if it exists
		if (trim($link) != '')
		{
			$breadcrumbs .= $link;
			// If not the last item in the breadcrumbs add the separator
			if ($i < $numItems -1)
			{
				$breadcrumbs .= ' '.$separator.' ';
			}
		}
	}

	if (!$numItems)
	{
		$breadcrumbs .= '&nbsp;';
	}

	// Close the breadcrumbs span
	$breadcrumbs .= '</span>';

	return $breadcrumbs;

}

/**
 * Set the breadcrumbs separator for the breadcrumbs display.
 *
 * @param	string	$custom	Custom xhtml complient string to separate the
 * items of the breadcrumbs
 * @return	string	Separator string
 * @since	1.1
 */
function setSeparator($custom = null)
{
	global $mainframe;

	/**
	 * If a custom separator has not been provided we try to load a template
	 * specific one first, and if that is not present we load the default separator
	 */
	if ($custom == null)
	{

		// Set path for what would be a template specific separator
		$tSepPath = 'templates/'.$mainframe->getTemplate().'/images/arrow.png';

		// Check to see if the template specific separator exists and if so, set it
		if (JFile::exists(JPATH_SITE."/$tSepPath"))
		{
			$_separator = '<img src="'.$tSepPath.'" border="0" alt="arrow" />';
		}
		else
		{

			// Template specific separator does not exist, use the default separator
			$dSepPath = '/images/M_images/arrow.png';

			// Check to make sure the default separator exists
			if (JFile::exists(JPATH_SITE.$dSepPath))
			{
				$_separator = '<img src="images/M_images/arrow.png" alt="arrow" />';
			}
			else
			{
				// The default separator does not exist either ... just use a bracket
				$_separator = '&gt;';
			}
		}
	}
	else
	{
		$_separator = $custom;
	}
	return $_separator;
}
?>