<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Checkin
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the Checkin component
 *
 * @static
 * @package		Joomla
 * @subpackage	Checkin
 * @since 1.0
 */
class CheckinViewCheckin extends JView
{
	protected $rows;

	function display($tpl = null)
	{
		global $mainframe, $option;

		// get parameters from the URL or submitted form
		$db			= &JFactory::getDBO();
		$nullDate	= $db->getNullDate();

		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('Global Check-in'), 'checkin.png');
		JToolBarHelper::help('screen.checkin');

		$tables = $db->getTableList();
		foreach ($tables as $tn) {
			// make sure we get the right tables based on prefix
			if (!preg_match("/^".$mainframe->getCfg('dbprefix')."/i", $tn)) {
				continue;
			}
			$fields = $db->getTableFields(array($tn));

			$foundCO = false;
			$foundCOT = false;
			$foundE = false;

			$foundCO	= isset($fields[$tn]['checked_out']);
			$foundCOT	= isset($fields[$tn]['checked_out_time']);
			$foundE		= isset($fields[$tn]['editor']);

			if ($foundCO && $foundCOT) {
				if ($foundE) {
					$query = 'SELECT checked_out, editor FROM '.$tn.' WHERE checked_out > 0';
				} else {
					$query = 'SELECT checked_out FROM '.$tn.' WHERE checked_out > 0';
				}
				$db->setQuery($query);
				$res = $db->query();
				$num = $db->getNumRows($res);

				if ($foundE) {
					$query = 'UPDATE '.$tn.' SET checked_out = 0, checked_out_time = '.$db->Quote($nullDate).', editor = NULL WHERE checked_out > 0';
				} else {
					$query = 'UPDATE '.$tn.' SET checked_out = 0, checked_out_time = '.$db->Quote($nullDate).' WHERE checked_out > 0';
				}
				$db->setQuery($query);
				$res = $db->query();

				if ($res == 1) {
					$rows[] = array(
							'table' => $tn,
							'checked_in' => $num,
					);
				}
			}
		}

		$this->assignRef('rows',		$rows);

		parent::display($tpl);
	}
}
