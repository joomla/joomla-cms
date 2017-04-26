<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Fictitious component router rule for unittesting
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class TestComponentRouterRule implements JComponentRouterRulesInterface
{
	/**
	* Router this rule belongs to
	*
	* @var JComponentRouterView
	* @since 3.4
	*/
	protected $router;

	/**
	* Class constructor.
	*
	* @param JComponentRouterView $router Router this rule belongs to
	*
	* @since 3.4
	*/
	public function __construct(JComponentRouterView $router)
	{
		$this->router = $router;
	}

	/**
	* Finds the right Itemid for this query
	*
	* @param array &$query The query array to process
	*
	* @return void
	*
	* @since 3.4
	*/
	public function preprocess(&$query)
	{
		$query['testrule'] = 'yes';
	}

	/**
	* Parse method
	*
	* @param   array  &$segments  The URL segments to parse
	* @param   array  &$vars      The vars that result from the segments
	*
	* @return void
	*
	* @since 3.4
	*/
	public function parse(&$segments, &$vars)
	{
		array_pop($segments);
		$vars['testparse'] = 'run';
	}

	/**
	* Build method
	*
	* @param   array  &$query     The vars that should be converted
	* @param   array  &$segments  The URL segments to create
	*
	* @return void
	*
	* @since 3.4
	*/
	public function build(&$query, &$segments)
	{
		array_pop($query);
		$segments[] = 'testrule-run';
	}
}
