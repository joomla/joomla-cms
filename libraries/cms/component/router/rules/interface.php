<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JComponentRouterRules interface for Joomla
 *
 * @package     Joomla.Libraries
 * @subpackage  Component
 * @since       3.4.0
 */
interface JComponentRouterRulesInterface
{
	/**
	 * Prepares a query set to be handed over to the build() method.
	 * This should complete a partial query set to work as a complete non-SEFed
	 * URL and in general make sure that all information is present and properly
	 * formatted. For example, the Itemid should be retrieved and set here.
	 * 
	 * @param   JComponentRouter  $router  The calling router object
	 * @param   array             $query   The query array to process
	 * 
	 * @return  void
	 * 
	 * @since   3.4.0
	 */
	public function preprocess(JComponentRouter &$router, &$query);

	/**
	 * Parses an URI to retrieve informations for the right route through
	 * the component.
	 * This method should retrieve all its input from its method arguments.
	 *
	 * @param   JComponentRouter  $router    The calling router object
	 * @param   array             $segments  The URL segments to parse
	 * @param   array             $vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   3.4.0
	 */
	public function parse(JComponentRouter &$router, &$segments, &$vars);

	/**
	 * Builds URI segments from a query to encode the necessary informations
	 * for a route in a human-readable URL.
	 * This method should retrieve all its input from its method arguments.
	 *
	 * @param   JComponentRouter  $router    The calling router object
	 * @param   array             $query     The vars that should be converted
	 * @param   array             $segments  The URL segments to create
	 *
	 * @return  void
	 *
	 * @since   3.4.0
	 */
	public function build(JComponentRouter &$router, &$query, &$segments);
}