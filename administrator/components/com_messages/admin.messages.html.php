<?php
/**
* @version $Id: admin.messages.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Messages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );


/**
 * @package Joomla
 * @subpackage Contact
 */
class messagesScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {

		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* List languages
	* @param array
	*/
	function view( &$lists ) {
		global $mosConfig_lang;

		$tmpl =& messagesScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editConfig() {
		global $mosConfig_lang;

		$tmpl =& messagesScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editConfig.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	function newMessage() {
		global $mosConfig_lang;

		$tmpl =& messagesScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'newMessage.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	function readMessage() {
		global $mosConfig_lang;

		$tmpl =& messagesScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'readMessage.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Messages
*/
class HTML_messages {
	function showMessages( &$rows, $pageNav, $search, $option, &$lists ) {
		global $_LANG;

		?>
		<form action="index2.php" method="post" name="adminForm" id="messagesform" class="adminform">

		<?php
		messagesScreens::view( $lists );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="1%">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="1%" class="title">
						<input type="checkbox" name="toggle" value=""  />
					</th>
					<th class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Subject' ), 'a.subject' ); ?>
					</th>
					<th width="10%" class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Read' ), 'a.state' ); ?>
					</th>
					<th width="15%" class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'From' ), 'user_from' ); ?>
					</th>
					<th width="15%" class="title">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Date' ), 'a.date_time' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="6" class="center">
						<?php echo $pageNav->getPagesLinks(); ?>
					</th>
				</tr>
				<tr>
					<td colspan="6" nowrap="nowrap" class="center">
						<?php echo $_LANG->_( 'Display Num' ) ?>
						<?php echo  $pageNav->getLimitBox() ?>
						<?php echo $pageNav->getPagesCounter() ?>
				</tr>
				</tfoot>

				<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
					$row =& $rows[$i];

					$link = 'index2.php?option=com_users&amp;task=editA&amp;id='. $row->user_id_from;

					if ( $row->state ) {
						$alt	= $_LANG->_( 'Read' );
						$img	= 'tick.png';
					} else {
						$alt 	= $_LANG->_( 'Unread' );
						$img	= 'publish_x.png';
					}
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php echo $i+1+$pageNav->limitstart;?>
						</td>
						<td width="5%">
							<?php echo mosHTML::idBox( $i, $row->message_id ); ?>
						</td>
						<td>
							<a href="#edit" onclick="return listItemTask('cb<?php echo $i;?>','view')" class="editlink">
								<?php echo $row->subject; ?>
							</a>
						</td>
						<td>
							<img src="images/<?php echo $img; ?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>"/>
						</td>
						<td>
							<a href="<?php echo $link; ?>" title="Go to User info" class="editlink">
								<?php echo $row->user_from; ?>
							</a>
						</td>
						<td>
							<?php echo mosFormatDate( $row->date_time, $_LANG->_( 'DATE_FORMAT_LC3' ) ); ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
				</table>
			</fieldset>
		</div>

		<input type="hidden" name="tOrder" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrder_old" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrderDir" value="" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function editConfig( &$vars, $option) {
		global $_LANG;
		?>
		<form action="index2.php?option=com_messages" method="post" name="adminForm">
		<?php
		messagesScreens::editConfig();
		?>

			<table class="adminform" id="editpage">
			<thead>
			<tr>
				<th colspan="2">
					<?php echo $_LANG->_( 'Settings' ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="2">
				</th>
			</tr>
			</tfoot>

			<tr>
				<td width="200">
					<?php echo $_LANG->_( 'Lock Inbox' ); ?>:
				</td>
				<td>
					<?php echo $vars['lock']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Mail me on new Message' ); ?>:
				</td>
				<td>
					<?php echo $vars['mail_on_new']; ?>
				</td>
			</tr>
			</table>
			</fieldset>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function viewMessage( &$row, $option ) {
		global $_LANG;
		?>
		<form action="index2.php" method="post" name="adminForm">
		<?php
		messagesScreens::readMessage();
		?>

			<table class="adminform" id="editpage" border="1">
			<thead>
			<tr>
				<th colspan="2">
					<?php echo $_LANG->_( 'Message' ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="2">
				</th>
			</tr>
			</tfoot>

			<tr>
				<td width="150px">
					<?php echo $_LANG->_( 'From' ); ?>:
				</td>
				<td bgcolor="#ffffff">
					<?php echo $row->user_from;?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Posted' ); ?>:
				</td>
				<td bgcolor="#ffffff">
					<?php echo mosFormatDate( $row->date_time, $_LANG->_( 'DATE_FORMAT_LC3' ) ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Subject' ); ?>:
				</td>
				<td bgcolor="#ffffff">
					<?php echo $row->subject;?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<?php echo $_LANG->_( 'Message' ); ?>:
				</td>
				<td bgcolor="#ffffff">
				<pre>
						<?php echo htmlspecialchars( $row->message );?>
				</pre>
				</td>
			</tr>
			</table>
			</fieldset>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="cid[]" value="<?php echo $row->message_id; ?>" />
		<input type="hidden" name="userid" value="<?php echo $row->user_id_from; ?>" />
		<input type="hidden" name="subject" value="Re: <?php echo $row->subject; ?>" />
		</form>
		<?php
	}

	function newMessage($option, $recipientslist, $subject ) {
		global $my;
		global $_LANG;

		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (form.subject.value == "") {
				alert( "<?php echo $_LANG->_( 'You must provide a subject.' ); ?>" );
			} else if (form.message.value == "") {
				alert( "<?php echo $_LANG->_( 'You must provide a message.' ); ?>" );
			} else if (getSelectedValue('adminForm','user_id_to') < 1) {
				alert( "<?php echo $_LANG->_( 'You must select a recipient.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<?php
		messagesScreens::newMessage();
		?>
		<div align="center" style="width: 100%">
			<table class="adminform" id="editpage">
			<thead>
			<tr>
				<th colspan="2">
					<?php echo $_LANG->_( 'Message' ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="2">
				</th>
			</tr>
			</tfoot>

			<tr>
				<td width="100">
					<label for="user_id_to">
						<?php echo $_LANG->_( 'To' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $recipientslist; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="subject">
						<?php echo $_LANG->_( 'Subject' ); ?>:
					</label>
				</td>
				<td>
					<input type="text" id="subject" name="subject" size="50" maxlength="100" class="inputbox" value="<?php echo $subject; ?>"/>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="message">
						<?php echo $_LANG->_( 'Message' ); ?>:
					</label>
				</td>
				<td>
					<textarea name="message" id="message" style="width: 95%" rows="40" class="inputbox"></textarea>
				</td>
			</tr>
			</table>
			</fieldset>
		</div>

		<input type="hidden" name="user_id_from" value="<?php echo $my->id; ?>" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="">
		</form>
		<?php
	}
}
?>