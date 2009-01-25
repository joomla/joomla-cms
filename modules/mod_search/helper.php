<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modSearchHelper {
	function getSearchImage($button_text) {
		$img = JHtml::_('image.site', 'searchButton.gif', '/images/M_images/', NULL, NULL, $button_text, null, 0);
		return $img;
	}
}