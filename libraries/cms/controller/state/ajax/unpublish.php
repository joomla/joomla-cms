<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerStateAjaxUnpublish extends JControllerStateAjaxBase
{
	/**
	 * @param JModelAdministrator $model
	 * @param array               $cid
	 *
	 * @see JControllerStateBase::execute()
	 */
	protected function updateRecordState($model, $cid)
	{
		$model->updateRecordState($cid, 'unpublish');
	}
} 