<?php
/**
 * @version $Id: contact.php 3690 2006-05-27 04:59:14Z eddieajau $
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if ( ( $params->get( 'address_check' ) > 0 ) &&  ( $contact->address || $contact->suburb  || $contact->state || $contact->country || $contact->postcode ) ) {
	?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<?php
	if ( $params->get( 'address_check' ) > 0 ) {
		?>
		<tr>
			<td rowspan="6" valign="top" width="<?php echo $params->get( 'column_width' ); ?>" >
			<?php
			echo $params->get( 'marker_address' );
			?>
			</td>
		</tr>
		<?php
	}
	?>
	<?php
	if ( $contact->address && $params->get( 'street_address' ) ) {
		?>
		<tr>
			<td valign="top">
			<?php
			echo nl2br($contact->address);
			?>
			</td>
		</tr>
		<?php
	}
	if ( $contact->suburb && $params->get( 'suburb' ) ) {
		?>
		<tr>
			<td valign="top">
			<?php
			echo $contact->suburb;
			?>
			</td>
		</tr>
		<?php
	}
	if ( $contact->state && $params->get( 'state' ) ) {
		?>
		<tr>
			<td valign="top">
			<?php
			echo $contact->state;
			?>
			</td>
		</tr>
		<?php
	}
	if ( $contact->country && $params->get( 'country' ) ) {
		?>
		<tr>
			<td valign="top">
			<?php
			echo $contact->country;
			?>
			</td>
		</tr>
		<?php
	}
	if ( $contact->postcode && $params->get( 'postcode' ) ) {
		?>
		<tr>
			<td valign="top">
			<?php
			echo $contact->postcode;
			?>
			</td>
		</tr>
		<?php
	}
	?>
	</table>
	<br />
	<?php
}
	if ( ($contact->email_to && $params->get( 'email' )) || $contact->telephone  || $contact->fax ) {
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<?php
		if ( $contact->email_to && $params->get( 'email' ) ) {
			?>
			<tr>
				<td width="<?php echo $params->get( 'column_width' ); ?>" >
				<?php
				echo $params->get( 'marker_email' );
				?>
				</td>
				<td>
				<?php
				echo $contact->email;
				?>
				</td>
			</tr>
			<?php
		}
		if ( $contact->telephone && $params->get( 'telephone' ) ) {
			?>
			<tr>
				<td width="<?php echo $params->get( 'column_width' ); ?>" >
				<?php
				echo $params->get( 'marker_telephone' );
				?>
				</td>
				<td>
				<?php
				echo nl2br($contact->telephone);
				?>
				</td>
			</tr>
			<?php
		}
		if ( $contact->fax && $params->get( 'fax' ) ) {
			?>
			<tr>
				<td width="<?php echo $params->get( 'column_width' ); ?>" >
				<?php
				echo $params->get( 'marker_fax' );
				?>
				</td>
				<td>
				<?php
				echo nl2br($contact->fax);
				?>
				</td>
			</tr>
			<?php
		}
		if ( $contact->mobile ) {
			?>
			<tr>
				<td width="<?php echo $params->get( 'column_width' ); ?>" >
				</td>
				<td>
				<?php
				echo nl2br($contact->mobile);
				?>
				</td>
			</tr>
			<?php
		}
		if ( $contact->webpage ) {
			?>
			<tr>
				<td width="<?php echo $params->get( 'column_width' ); ?>" >
				</td>
				<td>
					<a href="<?php echo $contact->webpage; ?>" target="_blank">
						<?php echo $contact->webpage; ?></a>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<br />
		<?php
	}
	if ( $contact->misc && $params->get( 'misc' ) )
	{
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td width="<?php echo $params->get( 'column_width' ); ?>" valign="top" >
			<?php
			echo $params->get( 'marker_misc' );
			?>
			</td>
			<td>
			<?php
			echo $contact->misc;
			?>
			</td>
		</tr>
		</table>
		<br />
		<?php
	}

?>