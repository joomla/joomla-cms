<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JCmsControllerStateOrderdown extends JCmsControllerStateOrderup
{
	/**
	 * (non-PHPdoc)
	 * @see JCmsControllerStateBase::execute()
	 */
	protected function updateRecordState($model, $cid)
	{
		$model->reorder($cid, 'down');
	}
}