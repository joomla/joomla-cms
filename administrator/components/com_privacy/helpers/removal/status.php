<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Data object communicating the status of whether the data for an information request can be removed.
 *
 * Typically, this object will only be used to communicate data will be removed.
 *
 * @since  3.9.0
 */
class PrivacyRemovalStatus
{
	/**
	 * Flag indicating the status reported by the plugin on whether the information can be removed
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	public $canRemove = true;

	/**
	 * A status message indicating the reason data can or cannot be removed
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	public $reason;
}
