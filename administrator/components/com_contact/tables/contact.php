<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


/**
 * @package		Joomla
 * @subpackage	Contact
 */
class TableContact extends JTable
{
	/** @var int Primary key */
	var $id 					= null;
	/** @var string */
	var $name 				= null;
	/** @var string */
	var $alias				= null;
	/** @var string */
	var $con_position 		= null;
	/** @var string */
	var $address 			= null;
	/** @var string */
	var $suburb 				= null;
	/** @var string */
	var $state 				= null;
	/** @var string */
	var $country 			= null;
	/** @var string */
	var $postcode 			= null;
	/** @var string */
	var $telephone 			= null;
	/** @var string */
	var $fax 				= null;
	/** @var string */
	var $misc 				= null;
	/** @var string */
	var $image 				= null;
	/** @var string */
	var $imagepos 			= null;
	/** @var string */
	var $email_to 			= null;
	/** @var int */
	var $default_con 		= null;
	/** @var int */
	var $published 			= 0;
	/** @var int */
	var $checked_out 		= 0;
	/** @var datetime */
	var $checked_out_time 	= 0;
	/** @var int */
	var $ordering 			= null;
	/** @var string */
	var $params 				= null;
	/** @var int A link to a registered user */
	var $user_id 			= null;
	/** @var int A link to a category */
	var $catid 				= null;
	/** @var int */
	var $access 				= null;
	/** @var string Mobile phone number(s) */
	var $mobile 				= null;
	/** @var string */
	var $webpage 			= null;

	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct( '#__contact_details', 'id', $db );
	}

	/**
	 * Overloaded check function
	 *
	 * @access public
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function check()
	{
		$this->default_con = intval( $this->default_con );

		jimport('joomla.filter.input');
		if (JFilterInput::checkAttribute(array ('href', $this->webpage))) {
			$this->_error = JText::_('Please provide a valid URL');
			return false;
		}

		// check for http on webpage
		if (strlen($this->webpage) > 0 && (!(eregi('http://', $this->webpage) || (eregi('https://', $this->webpage)) || (eregi('ftp://', $this->webpage))))) {
			$this->webpage = 'http://'.$this->webpage;
		}

		jimport('joomla.filter.output');
		$alias = JFilterOutput::stringURLSafe($this->name);

		if(empty($this->alias) || $this->alias === $alias ) {
			$this->alias = $alias;
		}

		return true;
	}
}
?>
