<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Privacy component HTML helper.
 *
 * @since  3.9.0
 */
class PrivacyHtmlHelper
{
	/**
	 * Render a status label
	 *
	 * @param   integer  $status  The item status
	 *
	 * @return  string
	 *
	 * @since   3.9.0
	 */
	public static function statusLabel($status)
	{
		switch ($status)
		{
			case 2:
				return '<span class="label label-success">' . JText::_('COM_PRIVACY_STATUS_COMPLETED') . '</span>';

			case 1:
				return '<span class="label label-info">' . JText::_('COM_PRIVACY_STATUS_CONFIRMED') . '</span>';

			case -1:
				return '<span class="label label-important">' . JText::_('COM_PRIVACY_STATUS_INVALID') . '</span>';

			default:
			case 0:
				return '<span class="label label-warning">' . JText::_('COM_PRIVACY_STATUS_PENDING') . '</span>';
		}
	}
}
