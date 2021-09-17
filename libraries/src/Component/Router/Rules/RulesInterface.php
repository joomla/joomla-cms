<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router\Rules;

defined('JPATH_PLATFORM') or die;

/**
 * RouterRules interface for Joomla
 *
 * @since  3.4
 */
interface RulesInterface
{
	/**
	 * Prepares a query set to be handed over to the build() method.
	 * This should complete a partial query set to work as a complete non-SEFed
	 * URL and in general make sure that all information is present and properly
	 * formatted. For example, the Itemid should be retrieved and set here.
	 *
	 * @param   array  &$query  The query array to process
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function preprocess(&$query);

	/**
	 * Parses a URI to retrieve informations for the right route through
	 * the component.
	 * This method should retrieve all its input from its method arguments.
	 *
	 * @param   array  &$segments  The URL segments to parse
	 * @param   array  &$vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function parse(&$segments, &$vars);

	/**
	 * Builds URI segments from a query to encode the necessary informations
	 * for a route in a human-readable URL.
	 * This method should retrieve all its input from its method arguments.
	 *
	 * @param   array  &$query     The vars that should be converted
	 * @param   array  &$segments  The URL segments to create
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function build(&$query, &$segments);
}
