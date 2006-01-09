<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Contact
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


/**
* @package Joomla
* @subpackage Contact
*/
class JContactView {


	function displaylist( &$categories, &$rows, &$current, $catid, &$params ) {
		global $Itemid, $hide_js;

		// used to show table rows in alternating colours
		$tabclass = array ('sectiontableentry1', 'sectiontableentry2');

		if ( $params->get( 'page_title' ) ) {
			?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $current->header; ?>
			</div>
			<?php
		}
		?>
		<form action="index.php" method="post" name="adminForm">

		<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
			<?php
			// show image
			if ( $current->cimage ) {
				?>
				<img src="<?php echo $current->cimage; ?>" align="<?php echo $current->cimage_position; ?>" hspace="6" alt="<?php echo JText::_( 'Web Links' ); ?>" />
				<?php
			}
			echo $current->cdescription;
			?>
			</td>
		</tr>
		<tr>
			<td>
			<?php
			if ( count( $rows ) ) {
				JContactView::showTable( $params, $rows, $catid, $tabclass );
			}
			?>
			</td>
		</tr>
		<tr>
			<td>&nbsp;

			</td>
		</tr>
		<tr>
			<td>
			<?php
			// Displays listing of Categories
			if ( ( $params->get( 'type' ) == 'category' ) && $params->get( 'other_cat' ) ) {
				JContactView::showCategories( $params, $categories, $catid );
			} else if ( ( $params->get( 'type' ) == 'section' ) && $params->get( 'other_cat_section' ) ) {
				JContactView::showCategories( $params, $categories, $catid );
			}
			?>
			</td>
		</tr>
		</table>
		</form>
		<?php
		// displays back button
		mosHTML::BackButton ( $params, $hide_js );
	}

	function viewContact( &$contact, &$params, $count, &$list, &$menu_params ) {
		global $mainframe, $Itemid;

		$template = $mainframe->getTemplate();
		$sitename = $mainframe->getCfg( 'sitename' );
		$hide_js = mosGetParam($_REQUEST,'hide_js', 0 );
		?>
		<script language="JavaScript" type="text/javascript">
		<!--
		function validate(){
			if ( ( document.emailForm.text.value == "" ) || ( document.emailForm.email.value.search("@") == -1 ) || ( document.emailForm.email.value.search("[.*]" ) == -1 ) ) {
				alert( "<?php echo JText::_( 'CONTACT_FORM_NC', true ); ?>" );
			} else {
			document.emailForm.action = "<?php echo sefRelToAbs("index.php?option=com_contact&Itemid=$Itemid"); ?>"
			document.emailForm.submit();
			}
		}
		//-->
		</script>
		<script type="text/javascript">
		<!--
		function ViewCrossReference( selSelectObject ){
			var links = new Array();
			<?php
			foreach ($list as $item) {
				echo "\n\t\t\tlinks[".$item->value."]='"
					. sefRelToAbs( 'index.php?option=com_contact&task=view&contact_id='. $item->value .'&Itemid='. $Itemid )
					. "';";
			}
			?>

			var sel = selSelectObject.options[selSelectObject.selectedIndex].value
			if (sel != "") {
				location.href = links[sel];
			}
		}
		//-->
		</script>
		<?php
		// For the pop window opened for print preview
		if ( $params->get( 'popup' ) ) {
			$mainframe->setPageTitle( $sitename .' - '. $contact->name );
			$mainframe->addCustomHeadTag( '<link rel="stylesheet" href="templates/'. $template .'/css/template_css.css" type="text/css" />' );
		}
		if ( $menu_params->get( 'page_title' ) ) {
			?>
			<div class="componentheading<?php echo $menu_params->get( 'pageclass_sfx' ); ?>">
				<?php echo $menu_params->get( 'header' ); ?>
			</div>
			<?php
		}
		?>

		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="contentpane<?php echo $menu_params->get( 'pageclass_sfx' ); ?>">
		<?php
		// displays Page Title
		JContactView::_writePageTitle( $params, $menu_params );

		// displays Contact Select box
		JContactView::_writeSelectContact( $contact, $params, $count );

		// displays Name & Positione
		JContactView::_writeContactName( $contact, $params, $menu_params );
		?>
		<tr>
			<td>
				<table border="0" width="100%">
				<tr>
					<td></td>
					<td rowspan="2" align="right" valign="top">
					<?php
					// displays Image
					JContactView::_writeImage( $contact, $params );
					?>
					</td>
				</tr>
				<tr>
					<td>
					<?php
					// displays Address
					JContactView::_writeContactAddress( $contact, $params );

					// displays Email & Telephone
					JContactView::_writeContactContact( $contact, $params );

					// displays Misc Info
					JContactView::_writeContactMisc( $contact, $params );
					?>
					</td>
				</tr>
				</table>
			</td>
			<td>&nbsp;</td>
		</tr>
		<?php
		// displays Email Form
		JContactView::_writeVcard( $contact, $params );
		// displays Email Form
		JContactView::_writeEmailForm( $contact, $params, $sitename, $menu_params );
		?>
		</table>
		<?php
		// display Close button in pop-up window
		mosHTML::CloseButton ( $params, $hide_js );

		// displays back button
		mosHTML::BackButton ( $params, $hide_js );
	}

	function noContact( &$params ) {
		?>
		<br />
		<br />
			<?php echo JText::_( 'There are no Contact Details listed.' );?>
		<br />
		<br />
		<?php
		// displays back button
		mosHTML::BackButton ( $params );
	}

	/**
	* Display Table of items
	*/
	function showTable( &$params, &$rows, $catid, $tabclass ) {
		global $Itemid;
		?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
		<?php
		if ( $params->get( 'headings' ) ) {
			?>
			<tr>
				<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php echo JText::_( 'Name' ); ?>
				</td>
				<?php
				if ( $params->get( 'position' ) ) {
					?>
					<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo JText::_( 'Position' ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'email' ) ) {
					?>
					<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo JText::_( 'Email' ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'telephone' ) ) {
					?>
					<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo JText::_( 'Phone' ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'fax' ) ) {
					?>
					<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo JText::_( 'Fax' ); ?>
					</td>
					<?php
				}
				?>
				<td width="100%"></td>
			</tr>
			<?php
		}

		$k = 0;
		foreach ($rows as $row) {
			$link = 'index.php?option=com_contact&amp;task=view&amp;contact_id='. $row->id .'&amp;Itemid='. $Itemid;
			?>
			<tr>
				<td width="25%" height="20" class="<?php echo $tabclass[$k]; ?>">
					<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo $row->name; ?></a>
				</td>
				<?php
				if ( $params->get( 'position' ) ) {
					?>
					<td width="25%" class="<?php echo $tabclass[$k]; ?>">
						<?php echo $row->con_position; ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'email' ) ) {
					if ( $row->email_to ) {
						$row->email_to = mosHTML::emailCloaking( $row->email_to, 1 );
					}
					?>
					<td width="20%" class="<?php echo $tabclass[$k]; ?>">
						<?php echo $row->email_to; ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'telephone' ) ) {
					?>
					<td width="15%" class="<?php echo $tabclass[$k]; ?>">
						<?php echo $row->telephone; ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'fax' ) ) {
					?>
					<td width="15%" class="<?php echo $tabclass[$k]; ?>">
						<?php echo $row->fax; ?>
					</td>
					<?php
				}
				?>
				<td width="100%"></td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		<?php
	}

	/**
	* Display links to categories
	*/
	function showCategories( &$params, &$categories, $catid ) {
		global $Itemid;
		?>
		<ul>
		<?php
		foreach ( $categories as $cat ) {
			if ( $catid == $cat->catid ) {
				?>
				<li>
					<b>
					<?php echo $cat->title;?>
					</b>
					&nbsp;
					<span class="small<?php echo $params->get( 'pageclass_sfx' ); ?>">
					(<?php echo $cat->numlinks;?>)
					</span>
				</li>
				<?php
			} else {
				$link = 'index.php?option=com_contact&amp;catid='. $cat->catid .'&amp;Itemid='. $Itemid;
				?>
				<li>
					<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo $cat->title;?></a>
					<?php
					if ( $params->get( 'cat_items' ) ) {
						?>
						&nbsp;
						<span class="small<?php echo $params->get( 'pageclass_sfx' ); ?>">
							(<?php echo $cat->numlinks;?>)
						</span>
						<?php
					}
					?>
					<?php
					// Writes Category Description
					if ( $params->get( 'cat_description' ) ) {
						echo '<br />';
						echo $cat->description;
					}
					?>
				</li>
				<?php
			}
		}
		?>
		</ul>
		<?php
	}

	function emailSent() {
		global $Itemid;
		$option = JRequest::getVar('option');
		?>
		<script>
		alert( "<?php echo JText::_( 'Thank you for your e-mail', true ); ?>" );
		document.location.href='<?php echo sefRelToAbs( 'index.php?option='. $option .'&Itemid='. $Itemid ); ?>';
		</script>
		<?php
	}

	function emailError() {
		global $Itemid;
		$option = JRequest::getVar('option');
		?>
		<script>
		alert( "<?php echo JText :: _('CONTACT_FORM_NC', true); ?>" );
		document.location.href='<?php echo sefRelToAbs( 'index.php?option='. $option .'&Itemid='. $Itemid ); ?>';
		</script>
		<?php
	}

	/**
	* Writes Page Title
	*/
	function _writePageTitle( &$params, &$menuParams ) {
		if ( $params->get( 'page_title' )  && !$params->get( 'popup' ) ) {
			?>
			<tr>
				<td class="componentheading<?php echo $menuParams->get( 'pageclass_sfx' ); ?>" colspan="2">
					<?php echo $params->get( 'header' ); ?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	* Writes Dropdown box to select contact
	*/
	function _writeSelectContact( &$contact, &$params, $count ) {
		if ( ( $count > 1 )  && !$params->get( 'popup' ) && $params->get( 'drop_down' ) ) {
			global $Itemid;
			?>
			<tr>
				<td colspan="2" align="center">
				<br />
				<form action="<?php echo sefRelToAbs( 'index.php?option=com_contact&amp;Itemid='. $Itemid ); ?>" method="post" name="selectForm" target="_top" id="selectForm">
					<?php echo JText::_( 'Select Contact' ); ?>:
					<br />
					<?php echo $contact->select; ?>
				</form>
				</td>
			</tr>
			<?php
		}
	}

	/**
	* Writes Name & Position
	*/
	function _writeContactName( &$contact, &$params, &$menu_params ) {
		global $Itemid, $hide_js;

		if ( $contact->name ||  $contact->con_position ) {
			if ( $contact->name && $params->get( 'name' ) ) {
				?>
				<tr>
					<td width="100%" class="contentheading<?php echo $menu_params->get( 'pageclass_sfx' ); ?>">
					<?php
					echo $contact->name;
					?>
					</td>
					<?php
					// displays Print Icon
					$print_link = 'index2.php?option=com_contact&amp;task=view&amp;contact_id='. $contact->id .'&amp;Itemid='. $Itemid .'&amp;pop=1';
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
		}
	}

	/*
	* Writes Image
	*/
	function _writeImage( &$contact, &$params ) {
		if ( $contact->image && $params->get( 'image' ) ) {
			?>
			<div style="float: right;">
			<img src="<?php echo JURL_SITE;?>/images/stories/<?php echo $contact->image; ?>" align="middle" alt="<?php echo JText::_( 'Contact' ); ?>" />
			</div>
			<?php
		}
	}

	/**
	* Writes Address
	*/
	function _writeContactAddress( &$contact, &$params ) {
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
					echo $contact->address;
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
	}

	/**
	* Writes Contact Info
	*/
	function _writeContactContact( &$contact, &$params ) {
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
					echo $contact->telephone;
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
					echo $contact->fax;
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
	}

	/**
	* Writes Misc Info
	*/
	function _writeContactMisc( &$contact, &$params ) {
		if ( $contact->misc && $params->get( 'misc' ) ) {
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
	}

	/**
	* Writes Email form
	*/
	function _writeVcard( &$contact, &$params ) {
		if ( $params->get( 'vcard' ) ) {
			?>
			<tr>
				<td colspan="2">
				<?php echo JText::_( 'Download information as a' );?>
				<a href="index2.php?option=com_contact&amp;task=vcard&amp;contact_id=<?php echo $contact->id; ?>&amp;no_html=1">
				<?php echo JText::_( 'VCard' );?>
				</a>
				</td>
			</tr>
			<?php
		}
	}

	/**
	* Writes Email form
	*/
	function _writeEmailForm( &$contact, &$params, $sitename, &$menu_params ) {
		global $Itemid;

		if ( $contact->email_to && !$params->get( 'popup' ) && $params->get( 'email_form' ) ) {
			?>
			<tr>
				<td colspan="2">
				<br />
				<?php echo $params->get( 'email_description' ) ?>
				<br /><br />
				<form action="<?php echo sefRelToAbs( 'index.php?option=com_contact&amp;Itemid='. $Itemid ); ?>" method="post" name="emailForm" target="_top" id="emailForm">
				<div class="contact_email<?php echo $menu_params->get( 'pageclass_sfx' ); ?>">
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
					<textarea cols="50" rows="10" name="text" id="contact_text" class="inputbox"></textarea>
					<?php
					if ( $params->get( 'email_copy' ) ) {
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
				<input type="hidden" name="con_id" value="<?php echo $contact->id; ?>" />
				<input type="hidden" name="sitename" value="<?php echo $sitename; ?>" />
				<input type="hidden" name="task" value="sendmail" />
				</form>
				<br />
				</td>
			</tr>
			<?php
		}
	}
}
?>