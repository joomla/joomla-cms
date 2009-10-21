<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Checkin Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @since		1.6
 */
class CheckinModelCheckin extends JModel
{
	/**
	 * Checks in requested tables
	 *
	 * @param  	array	An array of table names. Optional.
	 * @return	array	Checked in table names as keys and checked in item count as values
	 * @since	1.6
	 */
	public function checkin($tables = null)
	{
		$app 		= &JFactory::getApplication();
		$db 		= &$this->_db;
		$nullDate 	= $db->getNullDate();

		if (!is_array($tables)) {
			$tables = $db->getTableList();
		}

		// this array will hold table name as key and checked in item count as value
		$results = array();

		foreach ($tables as $tn) {
			// make sure we get the right tables based on prefix
			if (stripos($tn, $app->getCfg('dbprefix')) !== 0) {
				continue;
			}

			$fields = $db->getTableFields(array($tn));

			if (!(isset($fields[$tn]['checked_out']) && isset($fields[$tn]['checked_out_time']))) {
				continue;
			}

			$results[$tn] = 0;
			$query = 'UPDATE '.$db->nameQuote($tn)
				. ' SET checked_out = 0, checked_out_time = '.$db->Quote($nullDate)
				. (isset($fields[$tn]['editor']) ? ', editor = NULL' : '')
				. ' WHERE checked_out > 0';

			$db->setQuery($query);
			if ($db->query()) {
				$results[$tn] = $db->getAffectedRows();
			} else {
				continue;
			}
		}

		return $results;
	}
}