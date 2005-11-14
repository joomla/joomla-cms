<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Messages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Messages
*/
class mosMessage extends mosDBTable {
	/** @var int Primary key */
	var $message_id		= null;
	/** @var int */
	var $user_id_from	= null;
	/** @var int */
	var $user_id_to		= null;
	/** @var int */
	var $folder_id		= null;
	/** @var datetime */
	var $date_time		= null;
	/** @var int */
	var $state			= null;
	/** @var int */
	var $priority		= null;
	/** @var string */
	var $subject		= null;
	/** @var text */
	var $message		= null;

	/**
	* @param database A database connector object
	*/
	function mosMessage( &$db ) {
		$this->mosDBTable( '#__messages', 'message_id', $db );
	}

	function send( $from_id=null, $to_id=null, $subject=null, $message=null ) {
		global $database;
		;

		if (is_object( $this )) {
			$from_id 	= $from_id ? $from_id : $this->user_id_from;
			$to_id 		= $to_id ? $to_id : $this->user_id_to;
			$subject 	= $subject ? $subject : $this->subject;
			$message 	= $message ? $message : $this->message;
		}

		$query = "SELECT cfg_name, cfg_value"
		. "\n FROM #__messages_cfg"
		. "\n WHERE user_id = $to_id"
		;
		$database->setQuery( $query );
		$config = $database->loadObjectList( 'cfg_name' );
		$locked = @$config['lock']->cfg_value;
		$domail = @$config['mail_on_new']->cfg_value;

		if (!$locked) {

			$this->user_id_from = $from_id;
			$this->user_id_to 	= $to_id;
			$this->subject 		= $subject;
			$this->message 		= $message;
			$this->date_time 	= date( 'Y-m-d H:i:s' );

			if ($this->store()) {
				if ($domail) {
					$query = "SELECT email"
					. "\n FROM #__users"
					. "\n WHERE id = $to_id"
					;
					$database->setQuery( $query );
					$recipient = $database->loadResult();
					$subject 	= JText::_( 'A new private message has arrived' );
					$msg 		= JText::_( 'A new private message has arrived' );

					mosMail( $mosConfig_mailfrom, $mosConfig_fromname, $recipient, $subject, $msg );
				}
				return true;
			}
		} else {
			if (is_object( $this )) {
				$this->_error = JText::_( 'MESSAGE_FAILED' );
			}
		}
		return false;
	}
}
?>
