<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Component.Router
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_PLATFORM') or die;

interface JComponentRouter {
	/**
	 * Build method for URLs
	 * 
	 * @param array $query Array of query elements
	 * 
	 * @return array Array of URL segments
	 */
	public function build(&$query);
	
	/**
	 * Parse method for URLs
	 * 
	 * @param array $segments Array of URL string-segments
	 * 
	 * @return array Associative array of query values
	 */
	public function parse(&$segments);
}