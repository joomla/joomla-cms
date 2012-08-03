<?php
/**
 * NoNumber Framework Helper File: Assignments: Components
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Assignments: Components
 */
class NNFrameworkAssignmentsComponents
{
	var $_version = '12.6.4';

	/**
	 * passComponents
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passComponents(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		return $main->passSimple(strtolower($main->_params->option), $selection, $assignment);
	}
}