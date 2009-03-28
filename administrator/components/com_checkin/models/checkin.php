<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Members Model for JXtended Members.
 *
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @since		1.6
 */
class CheckinModelCheckin extends JModel
{
	public function checkin()
	{
		$app 		=& JFactory::getApplication();
		$db 		=& $this->_db;
		$tables 	= $db->getTableList();
		$nullDate 	= $db->getNullDate();
	
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
			try {
				$db->query();
				$results[$tn] = $db->getAffectedRows();
			} catch (JException $e) {
				continue;
			}
		}
		
		return $results;
	}
}
