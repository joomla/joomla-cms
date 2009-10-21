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
	public $id = null;
	/**
	 * @var varchar
	 */
	public $old_url = null;
	/**
	 * @var varchar
	 */
	public $new_url = null;
	/**
	 * @var varchar
	 */
	public $comment = null;
	/**
	 * @var int unsigned
	 */
	public $published = null;
	/**
	 * @var int unsigned
	 */
	public $created_date = null;
	/**
	 * @var int unsigned
	 */
	public $updated_date = null;

	/**
	 * Constructor
	 *
	 * @param	object	Database object
	 * @return	void
	 * @since	1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__redirect_links', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return boolean
	 */
	public function check()
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
