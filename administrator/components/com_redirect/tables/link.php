<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request.');

/**
 * Link Table for Redirect.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @version		1.6
 */
class RedirectTableLink extends JTable
{
	/**
	 * @var int
	 */
	var $id = null;
	/**
	 * @var varchar
	 */
	var $old_url = null;
	/**
	 * @var varchar
	 */
	var $new_url = null;
	/**
	 * @var varchar
	 */
	var $comment = null;
	/**
	 * @var int unsigned
	 */
	var $published = null;
	/**
	 * @var int unsigned
	 */
	var $created_date = null;
	/**
	 * @var int unsigned
	 */
	var $updated_date = null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	Database object
	 * @return	void
	 * @since	1.0
	 */
	function __construct(&$db)
	{
		parent::__construct('#__redirect_links', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return boolean
	 */
	function check()
	{
		// check for valid name
		if((trim($this->old_url)) == '') {
			$this->setError(JText::_('Redirect_Source_URL_Required'));
			return false;
		}
		// check for valid name
		if((trim($this->new_url)) == '') {
			$this->setError(JText::_('Redirect_Destination_URL_Required'));
			return false;
		}

		return true;
	}
}
