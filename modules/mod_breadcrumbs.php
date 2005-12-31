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

/*
 * Initialize variables
 */
$showHome = true;
$showComponent = true;

// Set the default separator
$separator = setSeparator();

/*
 * Perhaps we should have a parameter to control whether home is displayed in the 
 * pathway or not.
 */
if ($params->get( 'showHome' ) == false) {
	$showHome = false;
} 

/*
 * Do not show the content component item in the BreadCrumbs list
 */
if ($mainframe->getOption() == 'com_content') {
	$showComponent = false;
}

// Get the PathWay object from the application
$pathway =& $mainframe->getPathWay($showHome, $showComponent);


/*
 * This is the JTemplate method of displaying the BreadCrumbs for maximum flexibility
 */
//$tmpl =& JTemplate::getInstance();
//$tmpl->parse( 'breadcrumbs.html' );

//$tmpl->addVar   ( 'breadcrumbs-items', 'separator', $separator );
//$tmpl->addObject( 'breadcrumbs-items', $breadcrumbs );

//$tmpl->display( 'breadcrumbs' );

/*
 * This is the standard way of displaying the BreadCrumbs
 */
echo showBreadCrumbs($pathway, $separator);



/**
 * Get the breadcrumbs string in XHTML format for output to the page
 *
 * @param array $items Pathway items to build a BreadCrumbs string
 * @param string $separator BreadCrumbs separator string [HTML]
 * @return string XHTML Compliant breadcrumbs string
 * @since 1.1
 */
function showBreadCrumbs( $items, $separator ) {

	/*
	 * Initialize variables
	 */
	$breadcrumbs = '<span class="pathway">';
	$i = null;
	$numItems = count($items);

	for ($i = 0; $i < $numItems; $i ++) {

		// If a link is present create an html link, if not just use the name
		if (empty($items[$i]->link) || $numItems == $i + 1 ) {
			$link = $items[$i]->name;
		} else {
			$link = '<a href="'.sefRelToAbs($items[$i]->link).'" class="pathway">'.$items[$i]->name.'</a>';
		}

		$link = ampReplace($link);

		// Add the link if it exists
		if (trim($link) != '') {
			$breadcrumbs .= $link;
			// If not the last item in the breadcrumbs add the separator
			if ($i < $numItems - 1) {
				$breadcrumbs .= ' ' .$separator. ' ';
			}
		}
	}

	// Close the breadcrumbs span
	$breadcrumbs .= '</span>';

	return $breadcrumbs;

}

/**
 * Set the breadcrumbs separator for the breadcrumbs display.
 *
 * @param string $custom Custom xhtml complient string to separate the items of the breadcrumbs
 * @return string Separator string
 * @since 1.1
 */
function setSeparator($custom = null) {
	global $mainframe;

	/**
	 * If a custom separator has not been provided we try to load a template
	 * specific one first, and if that is not present we load the default separator
	 */
	if ($custom == null) {

		// Set path for what would be a template specific separator
		$tSepPath = 'templates/'.$mainframe->getTemplate().'/images/arrow.png';

		// Check to see if the template specific separator exists and if so, set it
		if (JFile::exists(JPATH_SITE."/$tSepPath")) {
			$_separator = '<img src="'.JURL_SITE.'/'.$tSepPath.'" border="0" alt="arrow" />';
		} else {

			// Template specific separator does not exist, use the default separator
			$dSepPath = '/images/M_images/arrow.png';

			// Check to make sure the default separator exists
			if (JFile::exists(JPATH_SITE.$dSepPath)) {
				$_separator = '<img src="'.JURL_SITE.'/images/M_images/arrow.png" alt="arrow" />';
			} else {
				// The default separator does not exist either ... just use a bracket
				$_separator = '&gt;';
			}
		}
	} else {
		$_separator = $custom;
	}
	return $_separator;
}
?>