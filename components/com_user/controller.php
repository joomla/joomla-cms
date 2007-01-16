<?php
/**
 * @version		$Id: controller.php 6138 2007-01-02 03:44:18Z eddiea $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.component.controller');

/**
 * User Component Controller
 *
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.5
 */
class UserController extends JController
{
	/**
	 * Method to display a user
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		global $mainframe;

		parent::display();
	}

	function edit()
	{
		global $mainframe, $Itemid, $option;

		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		if ( $user->get('guest')) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		JRequest::setVar('layout', 'form');

		parent::display();
	}

	function save( )
	{
		global $mainframe, $option;
		
		//check the token before we do anything else
		$token	= JUtility::getToken();
		if(!JRequest::getVar( $token, 0, 'post' )) {
			JError::raiseError(403, 'Request Forbidden');
		} 

		$user	=& JFactory::getUser();
		$session =& JFactory::getSession();

		$db 	=& JFactory::getDBO();
		$user_id = JRequest::getVar( 'id', 0, 'post', 'int' );

		// do some security checks
		if ($user->get('id') == 0 || $user_id == 0 || $user_id <> $user->get('id')) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		$post = JRequest::get( 'post' );

		$post['password']	= JRequest::getVar('password', '', 'post', 'string');
		$post['verifyPass']	= JRequest::getVar('verifyPass', '', 'post', 'string');

		// do a password safety check
		if(strlen($post['password'])) { // so that "0" can be used as password e.g.
			if($post['password'] != $post['verifyPass']) {
				JError::raiseError(500, JText::_( 'Passwords do not match', true ) );
			}
		}

		$user =& JUser::getInstance($user_id);
		$orig_username = $user->get('username');

		if (!$user->bind( $post )) {
			JError::raiseError(500, $user->getError() );
		}

		if (!$user->save()) {
			JError::raiseError(500, $user->getError() );
		}

		// check if username has been changed
		if ( $orig_username != $user->get('username') )
		{
			// change username value in session table
			$query = "UPDATE #__session"
				. "\n SET username = '$user->get('username')"
				. "\n WHERE username = '$orig_username'"
				. "\n AND userid = $user->get('id')"
				. "\n AND gid = $user->get('gid')"
				. "\n AND guest = 0"
				;
			$db->setQuery( $query );
			$db->query();

			$session->set('username', $user->get('username'));
		}

		$link = $_SERVER['HTTP_REFERER'];
		$mainframe->redirect( $link, JText::_( 'Your settings have been saved.' ) );
	}

	function checkin( )
	{
		global $mainframe, $option;

		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();
		$userid	= $user->get('id');

		// Editor usertype check
		$canEdit	= $user->authorize( 'action', 'edit', 'content', 'all' );
		$canEditOwn = $user->authorize( 'action', 'edit', 'content', 'own' );

		$nullDate = $db->getNullDate();
		if (!($canEdit || $canEditOwn || $userid > 0)) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		// security check to see if link exists in a menu
		$link = 'index.php?option=com_user&task=CheckIn';
		$query = "SELECT id"
			. "\n FROM #__menu"
			. "\n WHERE link LIKE '%$link%'"
			. "\n AND published = 1"
		;
		$db->setQuery( $query );
		$exists = $db->loadResult();
		if ( !$exists ) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		$lt = mysql_list_tables($mainframe->getCfg('db'));
		$k = 0;
		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		while (list($tn) = mysql_fetch_array($lt)) {
			// only check in the jos_* tables
			if (strpos( $tn, $db->_table_prefix ) !== 0) {
				continue;
			}
			$lf = mysql_list_fields($mainframe->getCfg('db'), "$tn");
			$nf = mysql_num_fields($lf);

			$checked_out = false;
			$editor = false;

			for ($i = 0; $i < $nf; $i++) {
				$fname = mysql_field_name($lf, $i);
				if ( $fname == "checked_out") {
					$checked_out = true;
				} else if ( $fname == "editor") {
					$editor = true;
				}
			}

			if ($checked_out)
			{
				if ($editor) {
					$query = "SELECT checked_out, editor"
					. "\n FROM $tn"
					. "\n WHERE checked_out > 0"
					. "\n AND checked_out = $userid"
					;
					$db->setQuery( $query );
				} else {
					$query = "SELECT checked_out"
					. "\n FROM $tn"
					. "\n WHERE checked_out > 0"
					. "\n AND checked_out = $userid"
					;
					$db->setQuery( $query );
				}
				$res = $db->query();
				$num = $db->getNumRows( $res );

				if ($editor) {
					$query = "UPDATE $tn"
					. "\n SET checked_out = 0, checked_out_time = '$nullDate', editor = NULL"
					. "\n WHERE checked_out > 0"
					;
					$db->setQuery( $query );
				} else {
					$query = "UPDATE $tn"
					. "\n SET checked_out = 0, checked_out_time = '$nullDate'"
					. "\n WHERE checked_out > 0"
					;
					$db->setQuery( $query );
				}
				$res = $db->query();

				if ($res == 1) {

					if ($num > 0) {
						echo "\n<tr class=\"row$k\">";
						echo "\n	<td width=\"250\">";
						echo JText::_( 'Checking table' );
						echo " - $tn</td>";
						echo "\n	<td>";
						echo JText::_( 'Checked in' ) ." <b>". $num ."</b> ". JText::_( 'items' );
						echo "</td>";
						echo "\n</tr>";
					}
					$k = 1 - $k;
				}
			}
		}
		?>
		<tr>
			<td colspan="2">
				<b><?php echo JText::_( 'CONF_CHECKED_IN' ); ?></b>
			</td>
		</tr>
		</table>
		<?php
	}
	
	function cancel() {
		$this->setRedirect( 'index.php' );
	}
}
?>