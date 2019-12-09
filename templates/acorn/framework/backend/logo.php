<?php

/**
 * @package     acorn.Framework
 * @subpackage  Logo Tab
 *
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Logo Tab
$logo = '';
$style = '';
$logoImage = $this->params->get('logoImage');
$brandText = $this->params->get('brandText');


/**
 * ==================================================
 * Favicon
 * ==================================================
 */
$favicon = $this->params->get('favicon');
if ($favicon) {
	$this->addFavicon(JURI::root() . $favicon);
} elseif (JFile::exists(JPath::clean(__DIR__ . '/../images/favicon.ico'))) {
	$this->addFavicon(JURI::base(false) . $this->template . '/images/favicon.ico');
}
/**
 * ==================================================
 * Logo
 * ==================================================
 */
// Logo image
if ($logoImage) {
	// Custom logo image
	$logo = '<a href="' . $this->baseurl . '/">'
			. '	<img src="' . JURI::root(true) . $logoImage . '" alt="' . $this->params->get('logoaltText') . '">'
			. '</a>';
}
/**
 * ==================================================
 * Brand Text
 * ==================================================
 */
if ($brandText && $this->params->get('extendedlogoParams') && !$logoImage) {
	$strippedbrandText = strip_tags($brandText);
	// Check for stupid JCE things like just space &/or p wrapper
	if ($strippedbrandText == '' || $strippedbrandText == '&nbsp;' || $strippedbrandText == ' ') {
		$brandText = '';
	}
	// Ok, its got other stuff then just sitename so lets just insert the site name and export the content
	else {
		$brandText = str_replace('{sitename}', $app->get('sitename'), $brandText);
	}
	$logo = '<div>' . $brandText . '</div>';
}

	/* ----- END LOGO ----- */
