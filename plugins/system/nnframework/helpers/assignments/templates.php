<?php
/**
 * NoNumber Framework Helper File: Assignments: Templates
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
 * Assignments: Templates
 */
class NNFrameworkAssignmentsTemplates
{
	var $_version = '12.6.4';

	/**
	 * passTemplates
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passTemplates(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		$app = JFactory::getApplication();
		$template = $app->getTemplate();

		return $main->passSimple($template, $selection, $assignment, 1);
	}
}