<?php
/**
 * @version $Id: contact.php 3690 2006-05-27 04:59:14Z eddieajau $
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if ( $contact->email_to && !$params->get( 'popup' ) && $params->get( 'email_form' ) ) {
	?>
	<tr>
		<td colspan="2">
		<br />
		<?php echo $params->get( 'email_description_text' ) ?>
		<br /><br />
		<form action="<?php echo sefRelToAbs( 'index.php?option=com_contact&amp;Itemid='. $Itemid ); ?>" method="post" name="emailForm" target="_top" id="emailForm">

		<div class="contact_email<?php echo $mParams->get( 'pageclass_sfx' ); ?>">
			<label for="contact_name">
				&nbsp;<?php echo JText::_( 'Enter your name' );?>:
			</label>
			<br />
			<input type="text" name="name" id="contact_name" size="30" class="inputbox" value="" />
			<br />
			<label for="contact_email">
				&nbsp;<?php echo JText::_( 'Email address' );?>:
			</label>
			<br />
			<input type="text" name="email" id="contact_email" size="30" class="inputbox" value="" />
			<br />
			<label for="contact_subject">
				&nbsp;<?php echo JText::_( 'Message subject' );?>:
			</label>
			<br />
			<input type="text" name="subject" id="contact_subject" size="30" class="inputbox" value="" />
			<br /><br />
			<label for="contact_text">
				&nbsp;<?php echo JText::_( 'Enter your message' );?>:
			</label>
			<br />
			<textarea cols="50" rows="10" name="text" id="body" class="inputbox"></textarea>
			<?php
			if ($params->get( 'email_copy' )) {
				?>
				<br />
					<input type="checkbox" name="email_copy" id="contact_email_copy" value="1"  />
					<label for="contact_email_copy">
						<?php echo JText::_( 'EMAIL_A_COPY' ); ?>
					</label>
				<?php
			}
			?>
			<br />
			<br />
			<input type="button" name="send" value="<?php echo JText::_( 'Send' ); ?>" class="button" onclick="validate()" />
		</div>

		<input type="hidden" name="option" value="com_contact" />
		<input type="hidden" name="contact_id" value="<?php echo $contact->id; ?>" />
		<input type="hidden" name="task" value="sendmail" />
		<input type="hidden" name="<?php echo mosHash( $app->getCfg('db') ); ?>" value="1" />
		</form>
		<br />
		</td>
	</tr>
	<?php
}
?>