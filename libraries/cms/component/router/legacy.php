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

/**
 * Defaultrouter for missing or legacy component routers
 *
 * @since 2.5
 */
class JComponentRouterLegacy implements JComponentRouter
{
	/**
	 * Name of the component
	 * 
	 * @var string
	 */
	protected $component;
	
	/**
	 * Constructor of JDefaultRouter
	 * 
	 * @param string $component Componentname without the com_ prefix this router should react upon
	 */
	public function __construct($component)
	{
		$this->component = $component;
	}
	
	/**
	 * Generic build function for missing or legacy component router
	 * 
	 * @param array $query Query-elements of the URL
	 * 
	 * @return array Array of segments of the URL
	 */
	public function build(&$query)
	{
		$function = $this->component.'BuildRoute';
		if(function_exists($function)) {
			$segments = $function($query);
			$total = count($segments);
			for ($i=0; $i<$total; $i++) {
				$segments[$i] = str_replace(':', '-', $segments[$i]);
			}
			return $segments;
		}
		return array();
	}

	/**
	 * Generic parse function for missing or legacy component router
	 * 
	 * @param array $segments Array of URL segments to parse
	 * 
	 * @return array Array of query elements
	 */
	public function parse(&$segments)
	{
		$function = $this->component.'ParseRoute';
		if(function_exists($function)) {
			$total = count($segments);
			for ($i=0; $i<$total; $i++)  {
				$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
			}

			return $function($segments);
		}
		return array();
	}
}
