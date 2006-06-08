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

	// Set some defaults for the menu item parameters
	$mParams->def('header', 				JComponentHelper::getMenuName() );
	$mParams->def('back_button', 			$app->getCfg('back_button'));
	$mParams->def('print', 					!$app->getCfg('hidePrint'));
	$mParams->def('email_description_text', JText::_('Send an Email to this Contact:'));
	// global print|pdf|email
	$mParams->def('icons', 					$app->getCfg('icons'));

	if ($contact->email_to && $mParams->get('email'))
	{
		// email cloacking
		$contact->email = mosHTML::emailCloaking($contact->email_to);
	}

	/*
	 * If the popup var is set, make some parameter changes
	 */
	$pop = JRequest::getVar('pop', 0, '', 'int');
	if ($pop)
	{
		$mParams->set('back_button', 0);
	}

	/*
	 * If e-mail description is set, lets prepare it for display
	 */
	if ($mParams->get('email_description'))
	{
		$mParams->set('email_description', $mParams->get('email_description_text'));
	} else
	{
		$mParams->set('email_description', '');
	}

	/*
	 * If there is no address information to display we set a flag so
	 * the display method for address does not get called in the view
	 * class
	 */
	if (!empty ($contact->address) || !empty ($contact->suburb) || !empty ($contact->state) || !empty ($contact->country) || !empty ($contact->postcode))
	{
		$mParams->set('address_check', 1);
	} else
	{
		$mParams->set('address_check', 0);
	}

	/*
	 * Time to manage the display mode for contact detail groups
	 */
	switch ($mParams->get('contact_icons')) {
		case 1 :
			// text
			$mParams->set('marker_address', 		JText::_('Address').": ");
			$mParams->set('marker_email', 		JText::_('Email').": ");
			$mParams->set('marker_telephone', 	JText::_('Telephone').": ");
			$mParams->set('marker_fax', 			JText::_('Fax').": ");
			$mParams->set('marker_misc', 		JText::_('Information').": ");
			$mParams->set('column_width', 		'100');
			break;

		case 2 :
			// none
			$mParams->set('marker_address', 		'');
			$mParams->set('marker_email', 		'');
			$mParams->set('marker_telephone', 	'');
			$mParams->set('marker_fax', 			'');
			$mParams->set('marker_misc', 		'');
			$mParams->set('column_width', 		'0');
			break;

		default :
			// icons
			$image1 = mosAdminMenus::ImageCheck('con_address.png', 	'/images/M_images/', $mParams->get('icon_address'), 		'/images/M_images/', JText::_('Address').": ", 		JText::_('Address').": ");
			$image2 = mosAdminMenus::ImageCheck('emailButton.png', 	'/images/M_images/', $mParams->get('icon_email'), 		'/images/M_images/', JText::_('Email').": ", 		JText::_('Email').": ");
			$image3 = mosAdminMenus::ImageCheck('con_tel.png', 		'/images/M_images/', $mParams->get('icon_telephone'), 	'/images/M_images/', JText::_('Telephone').": ", 	JText::_('Telephone').": ");
			$image4 = mosAdminMenus::ImageCheck('con_fax.png', 		'/images/M_images/', $mParams->get('icon_fax'), 			'/images/M_images/', JText::_('Fax').": ", 			JText::_('Fax').": ");
			$image5 = mosAdminMenus::ImageCheck('con_info.png', 	'/images/M_images/', $mParams->get('icon_misc'), 		'/images/M_images/', JText::_('Information').": ", 	JText::_('Information').": ");
			$mParams->set('marker_address', 		$image1);
			$mParams->set('marker_email', 		$image2);
			$mParams->set('marker_telephone', 	$image3);
			$mParams->set('marker_fax', 			$image4);
			$mParams->set('marker_misc',			$image5);
			$mParams->set('column_width', 		'40');
			break;
	}


	$hide_js = JRequest::getVar( 'hide_js', 0 );

	$pageclass_sfx = $mParams->get( 'pageclass_sfx' );


	?>
	<script language="JavaScript" type="text/javascript">
	<!--
	function validate(){
		if ( ( document.emailForm.text.value == "" ) || ( document.emailForm.email.value.search("@") == -1 ) || ( document.emailForm.email.value.search("[.*]" ) == -1 ) ) {
			alert( "<?php echo JText::_( 'CONTACT_FORM_NC', true ); ?>" );
		} else if ( ( document.emailForm.email.value.search(";") != -1 ) || ( document.emailForm.email.value.search(",") != -1 ) || ( document.emailForm.email.value.search(" ") != -1 ) ) {
			alert( "<?php echo JText::_( 'You cannot enter more than one email address', true ); ?>" );
		} else {
			document.emailForm.action = "<?php echo sefRelToAbs("index.php?option=com_contact&Itemid=$Itemid"); ?>"
			document.emailForm.submit();
		}
	}
	//-->
	</script>
	<?php
	if ( $mParams->get( 'page_title' ) && !$params->get( 'popup' ) ) {
		?>
		<div class="componentheading<?php echo $pageclass_sfx ?>">
			<?php echo $mParams->get( 'header' ); ?>
		</div>
		<?php
	}
	?>

	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="contentpane<?php echo $pageclass_sfx ?>">
	<?php

	// displays Contact Select box
	if ( $params->get( 'drop_down' ) && count( $contacts ) > 1) {
		?>
		<tr>
			<td colspan="2" align="center">
				<br />
				<form method="post" name="selectForm" target="_top" id="selectForm">
					<?php echo JText::_( 'Select Contact' ); ?>:
					<br />
					<?php echo mosHTML::selectList($contacts, 'contact_id', 'class="inputbox" onchange="this.form.submit()"', 'id', 'name', $contactId);?>
					<option type="hidden" name="option" value="com_contact" />
					<option type="hidden" name="Itemid" value="<?php echo $Itemid;?>" />
				</form>
			</td>
		</tr>
		<?php
	}

	// displays Name & Positione
	if ( $contact->name && $params->get( 'name' ) )
	{
		?>
		<tr>
			<td width="100%" class="contentheading<?php echo $pageclass_sfx; ?>">
			<?php
			echo $contact->name;
			?>
			</td>
			<?php
			// displays Print Icon
			$print_link = 'index2.php?option=com_contact&amp;view=contact&amp;contact_id='. $contact->id .'&amp;Itemid='. $Itemid .'&amp;pop=1';
			mosHTML::PrintIcon( $contact, $params, $hide_js, $print_link );
			?>
		</tr>
		<?php
	}
	if ( $contact->con_position && $params->get( 'position' ) ) {
		?>
		<tr>
			<td colspan="2">
			<?php
			echo $contact->con_position;
			?>
			<br /><br />
			</td>
		</tr>
		<?php
	}
	?>
	<tr>
		<td>
			<table border="0" width="100%">
			<tr>
				<td></td>
				<td rowspan="2" align="right" valign="top">
				<?php
				// displays Image
				if ( $contact->image && $params->get( 'image' ) ) {
					?>
					<div style="float: right;">
						<img src="images/stories/<?php echo $contact->image; ?>" align="middle" alt="<?php echo JText::_( 'Contact' ); ?>" />
					</div>
					<?php
				}
				?>
				</td>
			</tr>
			<tr>
				<td>
				<?php
				// displays Address
				// displays Email & Telephone
				// displays Misc Info
				include( '_default_address.php' );
				?>
				</td>
			</tr>
			</table>
		</td>
		<td>&nbsp;</td>
	</tr>
	<?php

	// displays Email Form
	if ( $params->get( 'vcard' ) ) {
		?>
		<tr>
			<td colspan="2">
			<?php echo JText::_( 'Download information as a' );?>
			<a href="index2.php?option=com_contact&amp;task=vcard&amp;contact_id=<?php echo $contact->id; ?>&amp;format=raw">
			<?php echo JText::_( 'VCard' );?>
			</a>
			</td>
		</tr>
		<?php
	}

	// displays Email Form
	include( '_default_emailform.php' );
	?>
		</table>
		<?php
	// display Close button in pop-up window
	mosHTML::CloseButton ( $params, $hide_js );

?>