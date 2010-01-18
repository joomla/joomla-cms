<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_search
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modSearchHelper
{
    function getSearchImage($button_text) {
	    $img = JHtml::_('image.site', 'searchButton.gif', '/images/system/', NULL, NULL, $button_text, null, 0);
		return $img;
	}
}